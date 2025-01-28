<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\DynamicCard;
use App\Entity\NetworkStatistic;
use App\Entity\NetworkStatisticTimeFrame;
use App\Form\NetworkChartType;
use App\Model\MobileSignalInfo;
use App\Model\TransmissionSettings;
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;
use App\Service\NetworkUsageProviderSettings;
use App\Service\SimpleSettingsService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use if0xx\HuaweiHilinkApi\Router;
use SimpleXMLElement;
use Throwable;

final class NetworkUsageService
{
    public const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';
    public const NETWORK_USAGE_PROVIDER_NONE = 'NONE';
    public const NETWORK_USAGE_PROVIDER_ROUTER_OS = 'ROUTER_OS';

    public function __construct(
        private AlexeyTranslator $translator,
        private EntityManagerInterface $em,
        private MikrotikService $mikrotikService,
        private NetworkStatisticRepository $networkStatisticRepository,
        private NetworkStatisticTimeFrameRepository $networkStatisticTimeFrameRepository,
        private NetworkUsageProviderSettings $networkUsageProviderSettings,
        private SimpleCacheService $simpleCacheService,
        private SimpleSettingsService $simpleSettingsService,
    ) {
    }

    public function updateStats(): void
    {
        $stat = $this->getCurrentStatistic();
        if ($stat instanceof NetworkStatistic) {
            $this->em->persist($stat);
            $this->em->flush();
        }
    }

    public function cleanUpStats(): void
    {
        $this->networkStatisticRepository->dropObsoleteRecords();
    }

    public function getCurrentStatistic(): ?NetworkStatistic
    {
        $type = $this->networkUsageProviderSettings->getProviderType();
        $stat = null;
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            $stat = $this->getCurrentStatisticFromHuawei();
        } elseif ($type === self::NETWORK_USAGE_PROVIDER_ROUTER_OS) {
            $stat = $this->getCurrentStatisticFromRouterOs();
            $this->mikrotikService->handleSimCardBugIfOccurs();
        } elseif ($type === '' || $type === self::NETWORK_USAGE_PROVIDER_NONE) {
            // No settings, no work, great!
        } else {
            throw new \Exception('Unknown network usage provider.');
        }
        return $stat;
    }

    public function getLatestStatistic(): ?NetworkStatistic
    {
        $latest = $this->networkStatisticRepository->getLatestOne();
        return $latest;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDataForChart(string $chartDataType, string $locale): array
    {
        $chartData = [
            'labels' => [],
            'datasets' => [],
            'bonusPayload' => [],
        ];
        $today = new DateTime('today');
        $now = new DateTime('now');
        if ($chartDataType === NetworkChartType::CHART_TYPE_TODAY) {
            $chartData = $this->prepareDataForChart(dateFrom: $today, timeFormat: 'H:i:s');
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_WEEK) {
            $shift = new DateInterval('P1W');
            $today->sub($shift);
            $chartData = $this->prepareDataForChart($today);
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_HOURS_2) {
            $shift = new DateInterval('PT2H');
            $now->sub($shift);
            $chartData = $this->prepareDataForChart(dateFrom: $now, timeFormat: 'H:i:s');
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_HOURS_48) {
            $shift = new DateInterval('PT48H');
            $now->sub($shift);
            $chartData = $this->prepareDataForChart($now);
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_MINUTES_TEN) {
            $shift = new DateInterval('PT10M');
            $now->sub($shift);
            $chartData = $this->prepareDataForChart(dateFrom: $now, timeFormat: 'H:i:s');
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_BILLING_FRAME) {
            $currentStat = $this->getLatestStatistic();
            $billingStart = $currentStat->getTimeFrame()->getBillingFrameStart();
            $chartData = $this->prepareDataForChart($billingStart);
        }

        $chartData['bonusPayload']['current_traffic_left'] = 0;
        $chartData['bonusPayload']['current_transfer_rate_left'] = 0;
        $chartData['bonusPayload']['current_transfer_rate'] = 0;
        $chartData['bonusPayload']['current_billing_frame_end'] = 0;

        $latestStat = $this->getLatestStatistic();
        if ($latestStat instanceof NetworkStatistic) {
            $chartData['bonusPayload']['current_traffic_left']
                = $latestStat->getTrafficLeftReadable(4) . ' | ' .
                $latestStat->getTrafficLeftReadable(4, TransmissionSettings::TARGET_SPEED_FRAME_DAY);
            $chartData['bonusPayload']['current_transfer_rate_left']
                = $latestStat->getTransferRateLeftReadable(4) . ' | ' .
                $latestStat->getTransferRateLeftReadable(4, TransmissionSettings::TARGET_SPEED_FRAME_DAY);
            $chartData['bonusPayload']['current_transfer_rate']
                = $latestStat->getTotalSpeedFromReferencePointReadable();
            $chartData['bonusPayload']['current_billing_frame_end']
                = $latestStat->getTimeFrame()->getBillingFrameEndReadable($locale);
        }

        try {
            $transmissionSettings = new TransmissionSettings();
            $transmissionSettings->selfConfigure($this->simpleSettingsService);
            $stat = $this->getLatestStatistic();
            $throttling = ($stat instanceof NetworkStatistic) ? $transmissionSettings->getProposedThrottleSpeed(
                speedLeft: $this->getLatestStatistic()->getTransferRateLeft(
                    $transmissionSettings->getTargetFrame()
                )
            ) : 0;
            $throttling .= ' kB/s';
        } catch (\Exception $e) {
            $throttling = 'N. A.';
        }
        $chartData['bonusPayload']['current_throttling'] = $throttling;

        return $chartData;
    }

    public function getDynacard(string $property, string $locale): DynamicCard
    {
        // TODO: move transmissionSettings to constructor
        $networkStatistics = $this->getLatestStatistic();
        $headerValue = '';
        if ($networkStatistics instanceof NetworkStatistic) {
            if ($property === 'optimal_speed') {
                $transmissionSettings = new TransmissionSettings();
                $transmissionSettings->selfConfigure($this->simpleSettingsService);
                $headerValue = $networkStatistics->getTransferRateLeftReadable(
                    precision: 4,
                    frameWidth: $transmissionSettings->getTargetFrame()
                );
            } elseif ($property === 'traffic_left') {
                $transmissionSettings = new TransmissionSettings();
                $transmissionSettings->selfConfigure($this->simpleSettingsService);
                $headerValue = $networkStatistics->getTrafficLeftReadable(
                    precision: 4,
                    frameWidth: $transmissionSettings->getTargetFrame(),
                );
            } elseif ($property === 'billing_window_end') {
                $headerValue = $networkStatistics->getTimeFrame()->getBillingFrameEndReadable(locale: $locale);
            }
        }
        $dynaCard = new DynamicCard();
        $dynaCard->setHeaderText(
            $this->translator->translateString(translationId: 'menu_record', module: 'network_usage')
        );
        $dynaCard->setHeaderValue($headerValue);
        $dynaCard->setFooterValue(
            $this->translator->translateString(translationId: $property, module: 'network_usage')
        );
        return $dynaCard;
    }

    public function getMobileSignalInfo(Router $router): MobileSignalInfo
    {
        // TODO: refactor, method only used for Huawei
        $info = new MobileSignalInfo($this->simpleCacheService);
        $info->fetchedAt = new DateTime('now');
        $type = $this->networkUsageProviderSettings->getProviderType();
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            try {
                $status = $router->getStatus();
                $signalMax = (int)$status->maxsignal;
                $signalCurrent = (int)$status->SignalIcon;
                $info->signalStrengthPercent = (int)($signalMax / $signalCurrent * 100);
            } catch (\Exception $e) {
                $info->error = $e->getMessage();
                $info->errorOn = 'basic_info';
                return $info;
            }

            try {
                $signal = $router->generalizedGet('api/device/signal');
                $info->band = (string)$signal->band;
                $info->cellId = (int)$signal->cell_id;
                $info->pci = (int)$signal->pci;
                $info->plmn = (int)$signal->plmn;
                $info->rsrp = (int)$signal->rsrp;
                $info->rsrq = (float)$signal->rsrq;
                $info->rssi = (int)$signal->rssi;
                $info->sinr = (float)$signal->sinr;
                $info->txpower = (string)$signal->txpower;
            } catch (\Exception $e) {
                $info->error = $e->getMessage();
                $info->errorOn = 'advanced_info';
                return $info;
            }
        }

        return $info;
    }

    /**
     * @return array<string,mixed>
     */
    private function prepareDataForChart(DateTime $dateFrom, string $timeFormat = 'd.m H:i'): array
    {
        $data = [];
        $labels = [];
        $datasets = [];
        $datasets['speed_relative'] = [
            'label' => $this->translator->translateString(
                translationId: 'current_speed',
                module: 'network_usage',
            ) . ' (kB/s)',
            'lineTension' => 0.3,
            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
            'borderColor' => 'rgba(78, 115, 223, 1)',
            'pointRadius' => 3,
            'pointBackgroundColor' => 'rgba(78, 115, 223, 1)',
            'pointBorderColor' => 'rgba(78, 115, 223, 1)',
            'pointHoverRadius' => 3,
            'pointHoverBackgroundColor' => 'rgba(78, 115, 223, 1)',
            'pointHoverBorderColor' => 'rgba(78, 115, 223, 1)',
            'pointHitRadius' => 10,
            'pointBorderWidth' => 2,
            'data' => [],
        ];
        $datasets['speed_left'] = [
            'label' => $this->translator->translateString(
                translationId: 'optimal_speed',
                module: 'network_usage',
            ) . ' (kB/s)',
            'lineTension' => 0.3,
            'backgroundColor' => 'rgba(78, 222, 223, 0.05)',
            'borderColor' => 'rgba(78, 222, 223, 1)',
            'pointRadius' => 3,
            'pointBackgroundColor' => 'rgba(78, 222, 223, 1)',
            'pointBorderColor' => 'rgba(78, 222, 223, 1)',
            'pointHoverRadius' => 3,
            'pointHoverBackgroundColor' => 'rgba(78, 222, 223, 1)',
            'pointHoverBorderColor' => 'rgba(78, 222, 223, 1)',
            'pointHitRadius' => 10,
            'pointBorderWidth' => 2,
            'data' => [],
        ];
        $datasets['speed_left_midnight'] = [
            'label' => $this->translator->translateString(
                translationId: 'optimal_speed_midnight',
                module: 'network_usage',
            ) . ' (kB/s)',
            'lineTension' => 0.3,
            'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
            'borderColor' => 'rgba(42, 79, 11, 1)',
            'pointRadius' => 3,
            'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
            'pointBorderColor' => 'rgba(42, 79, 11, 1)',
            'pointHoverRadius' => 3,
            'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
            'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
            'pointHitRadius' => 10,
            'pointBorderWidth' => 2,
            'data' => [],
        ];
        $now = new DateTime('now');
        $networkStatistics = $this->getPreparedEntitiesForChart($dateFrom, $now);

        /**
         * @var NetworkStatistic $stat
         */
        foreach ($networkStatistics as $stat) {
            $labels[] = $stat->getProbingTime()->format($timeFormat);
            $datasets['speed_relative']['data'][] = round(($stat->getTotalSpeedFromReferencePoint() / 1024), 4);
            $datasets['speed_left']['data'][] = round(($stat->getTransferRateLeft() / 1024), 4);
            $datasets['speed_left_midnight']['data'][] =
                round(($stat->getTransferRateLeft(TransmissionSettings::TARGET_SPEED_FRAME_DAY) / 1024), 4);
        }
        $data['labels'] = $labels;
        $data['datasets'] = $datasets;
        return $data;
    }

    private function getPreparedEntitiesForChart(DateTime $dateFrom, DateTime $dateTo, int $maxRecords = 50): array
    {
        // TODO: Get grouped by networkStatisticTimeFrame and drop older frames
        $networkStatistics = $this->networkStatisticRepository->getOrderedFromTimeRange($dateFrom, $dateTo);
        $count = count($networkStatistics);
        if ($count > $maxRecords) {
            $loosenEntities = [];
            $selectEveryNth = (int)($count / $maxRecords);
            foreach ($networkStatistics as $key => $entity) {
                if ($key % $selectEveryNth === 0) {
                    $loosenEntities[] = $entity;
                }
            }
            $networkStatistics = $loosenEntities;
        }
        $length = count($networkStatistics) - 1;
        foreach ($networkStatistics as $key => $stat) {
            if ($key < $length) {
                if ($stat->getTimeFrame() === $networkStatistics[$key + 1]->getTimeFrame()) {
                    $networkStatistics[$key + 1]->setReferencePoint($stat);
                }
            }
        }
        array_shift($networkStatistics); //first one can have bad statistics
        return $networkStatistics;
    }

    private function getCurrentStatisticFromHuawei(): ?NetworkStatistic
    {
        try {
            $router = new Router();
            $router->setAddress($this->networkUsageProviderSettings->getAddress());
            $router->login('admin', $this->networkUsageProviderSettings->getPassword());

            /** @var SimpleXMLElement $monthStats */
            $monthStats = $router->getMonthStats();

            /** @var SimpleXMLElement $startDate */
            $startDate = $router->generalizedGet('api/monitoring/start_date');

            $currentMonthDownload = (int)$monthStats->CurrentMonthDownload;
            $currentMonthUpload = (int)$monthStats->CurrentMonthUpload;
            $monthLastClearTime = (string)$monthStats->MonthLastClearTime;
            $trafficMaxLimit = (int)$startDate->trafficmaxlimit;
            $monthStart = new DateTime($monthLastClearTime);
            $monthEnd = clone $monthStart;
            $monthEnd->add(new DateInterval('P1M'));
            $monthEnd->sub(new DateInterval('PT1S'));

            $stat = new NetworkStatistic();
            $stat->setDataDownloadedInFrame($currentMonthDownload);
            $stat->setDataUploadedInFrame($currentMonthUpload);
            $timeFrame = $this->getTimeFrame($monthStart, $monthEnd, $trafficMaxLimit);
            $stat->setTimeFrame($timeFrame);

            $mobileStat = $this->getMobileSignalInfo(router: $router);
            if (
                strlen($mobileStat->error) === 0
                && $mobileStat->rsrq < 0
                && $mobileStat->rssi < 0
                && $mobileStat->rsrp < 0
            ) {
                $mobileStat->save();
            }

            return $stat;
        } catch (\Exception) {
            return null;
        }
    }

    public function getCurrentStatisticFromRouterOs(): ?NetworkStatistic
    {
        $scheduleReset = false;
        $totalRxBytes = 0;
        $totalTxBytes = 0;
        $lastUptime = null;

        foreach ($this->mikrotikService->getInterfaces() as $interfaceInfo) {
            if ($interfaceInfo['running'] === 'true' && $interfaceInfo['disabled'] === 'false') {
                if ($interfaceInfo['type'] === 'lte') {
                    $lastUptime = new DateTime($interfaceInfo['last-link-up-time']);
                    $rxBytes = (int)$interfaceInfo['rx-byte'];
                    $txBytes = (int)$interfaceInfo['tx-byte'];

                    $totalRxBytes += $rxBytes;
                    $totalTxBytes += $txBytes;
                }
            }
        }

        if ($lastUptime === null) {
            return null;
        }

        $trafficMaxLimit = $this->networkUsageProviderSettings->getMonthlyLimitGB();
        $trafficMaxLimit *= 1024 * 1024 * 1024;

        $monthStart = $this->getLastMonthResetTimeByUserDeclaration();
        $monthEnd = clone $monthStart;
        $monthEnd->add(new DateInterval('P1M'));
        $monthEnd->sub(new DateInterval('PT1S'));
        $timeFrame = $this->getTimeFrame($monthStart, $monthEnd, $trafficMaxLimit);

        $latestStat = $this->networkStatisticRepository->getLatestOne(probingTimeMax: $lastUptime);

        $latestStatFound = $latestStat instanceof NetworkStatistic;
        $latestStatFromSameTimeFrame = (true === $latestStatFound) && ($latestStat->getTimeFrame() === $timeFrame);

        $currentMonthDownload = $totalRxBytes;
        $currentMonthUpload = $totalTxBytes;

        if (true === $latestStatFound) {
            if (true === $latestStatFromSameTimeFrame) {
                $currentMonthDownload += $latestStat->getDataDownloadedInFrame();
                $currentMonthUpload += $latestStat->getDataUploadedInFrame();
            } else {
                $scheduleReset = true;
                $currentMonthDownload = 0;
                $currentMonthUpload = 0;
            }
        }

        $stat = new NetworkStatistic();
        $stat->setTimeFrame($timeFrame);
        $stat->setDataDownloadedInFrame($currentMonthDownload);
        $stat->setDataUploadedInFrame($currentMonthUpload);

        $mobileStat = $this->getMobileSignalInfoMikrotik();
        if ($mobileStat instanceof MobileSignalInfo) {
            $mobileStat->save();
        }

        if (true === $scheduleReset) {
            // Let's reboot so all counters start from 0
            $this->mikrotikService->powerCycleMikrotik(force: false, shortCycle: false);
        }

        return $stat;
    }

    private function getMobileSignalInfoMikrotik(): ?MobileSignalInfo
    {
        $info = new MobileSignalInfo($this->simpleCacheService);
        $info->fetchedAt = new DateTime('now');
        $hasEnabledLteInterface = false;

        try {
            foreach ($this->mikrotikService->getInterfaces() as $interfaceInfo) {
                if ($interfaceInfo['running'] === 'true') {
                    if ($interfaceInfo['type'] === 'lte') {
                        if (false === $hasEnabledLteInterface) {
                            $hasEnabledLteInterface = ($interfaceInfo['disabled'] === 'false');
                        }
                        $lteStatistics = $this->mikrotikService->getLteStatistics(interfaceId: $interfaceInfo['.id']);
                        $info->rsrq = (float) $lteStatistics[0]['rsrq'];
                        $info->rsrp = (int) $lteStatistics[0]['rsrp'];
                        $info->sinr = (float) $lteStatistics[0]['sinr'];
                        $info->cellId = (int) $lteStatistics[0]['current-cellid'];
                        $info->pci = (int) $lteStatistics[0]['phy-cellid'];
                        if (isset($lteStatistics[0]['earfcn'])) {
                            $info->band = (string) $lteStatistics[0]['earfcn'];
                        }
                        if (isset($lteStatistics[0]['primary-band'])) {
                            $info->band = (string) $lteStatistics[0]['primary-band'];
                            if (isset($lteStatistics[0]['ca-band'])) {
                                $info->band .= ' + ' . (string) $lteStatistics[0]['ca-band'];
                            }
                        }
                        if (isset($lteStatistics[0]['rssi'])) {
                            $info->rssi = (int) $lteStatistics[0]['rssi'];
                        }
                        $info->signalStrengthPercent = 0;
                        $info->txpower = 'CQI: ' . (string) $lteStatistics[0]['cqi'];
                    }
                }
            }
        } catch (Throwable) {
            return null;
        }

        if (false === $hasEnabledLteInterface) {
            return null;
        }
        return $info;
    }

    private function getTimeFrame(
        DateTime $frameStart,
        DateTime $frameEnd,
        int $frameDataLimit
    ): NetworkStatisticTimeFrame {
        $timeFrame = $this->networkStatisticTimeFrameRepository->findOneBy([
            'billingFrameStart' => $frameStart,
        ]);
        if (!$timeFrame instanceof NetworkStatisticTimeFrame) {
            $timeFrame = new NetworkStatisticTimeFrame();
        }

        $timeFrame->setBillingFrameEnd($frameEnd);
        $timeFrame->setBillingFrameDataLimit($frameDataLimit);
        $timeFrame->setBillingFrameStart($frameStart);

        $this->em->persist($timeFrame);
        $this->em->flush();

        return $timeFrame;
    }

    private function getLastMonthResetTimeByUserDeclaration(): DateTime
    {
        $billingDay = $this->networkUsageProviderSettings->getBillingDay();
        $now = new DateTime('now');
        $now->setTime(0, 0);

        $currentDay = intval($now->format('d'));

        // TODO will crash on 30th day of month or 29th of february etc. if billing is on 31st
        // needs some logic improvements
        // $lastDayOfCurrentMonth = intval($now->format('t'));

        if (($currentDay < $billingDay)) {
            $now->sub(new DateInterval('P1M'));
        }

        $now->setDate(
            year: intval($now->format('Y')),
            month: intval($now->format('m')),
            day: $billingDay
        );

        return $now;
    }
}
