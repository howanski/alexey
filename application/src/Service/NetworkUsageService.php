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

final class NetworkUsageService
{
    public const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';
    public const NETWORK_USAGE_PROVIDER_NONE = 'NONE';

    public function __construct(
        private AlexeyTranslator $translator,
        private EntityManagerInterface $em,
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

    public function getCurrentStatistic(): NetworkStatistic|null
    {
        $type = $this->networkUsageProviderSettings->getProviderType();
        $stat = null;
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            $stat = $this->getCurrentStatisticFromHuawei();
        } elseif ($type === '' || $type === self::NETWORK_USAGE_PROVIDER_NONE) {
            // No settings, no work, great!
        } else {
            throw new \Exception('Unknown network usage provider.');
        }
        return $stat;
    }

    public function getLatestStatistic()
    {
        $latest = $this->networkStatisticRepository->getLatestOne();
        return $latest;
    }

    public function getDataForChart(string $chartDataType, string $locale): array
    {
        $chdata = [
            'labels' => [],
            'datasets' => [],
            'bonusPayload' => [],
        ];
        $today = new DateTime('today');
        $now = new DateTime('now');
        if ($chartDataType === NetworkChartType::CHART_TYPE_TODAY) {
            $chdata = $this->prepareDataForChart(dateFrom: $today, timeFormat: 'H:i:s');
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_WEEK) {
            $shift = new DateInterval('P1W');
            $today->sub($shift);
            $chdata = $this->prepareDataForChart($today);
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_HOURS_2) {
            $shift = new DateInterval('PT2H');
            $now->sub($shift);
            $chdata = $this->prepareDataForChart(dateFrom: $now, timeFormat: 'H:i:s');
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_HOURS_48) {
            $shift = new DateInterval('PT48H');
            $now->sub($shift);
            $chdata = $this->prepareDataForChart($now);
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_MINUTES_TEN) {
            $shift = new DateInterval('PT10M');
            $now->sub($shift);
            $chdata = $this->prepareDataForChart(dateFrom: $now, timeFormat: 'H:i:s');
        } elseif ($chartDataType === NetworkChartType::CHART_TYPE_BILLING_FRAME) {
            $currentStat = $this->getLatestStatistic();
            $billingStart = $currentStat->getTimeFrame()->getBillingFrameStart();
            $chdata = $this->prepareDataForChart($billingStart);
        }

        $chdata['bonusPayload']['current_traffic_left'] = 0;
        $chdata['bonusPayload']['current_transfer_rate_left'] = 0;
        $chdata['bonusPayload']['current_transfer_rate_left_midnight'] = 0;
        $chdata['bonusPayload']['current_transfer_rate'] = 0;
        $chdata['bonusPayload']['current_billing_frame_end'] = 0;

        $latestStat = $this->getLatestStatistic();
        if ($latestStat instanceof NetworkStatistic) {
            $chdata['bonusPayload']['current_traffic_left']
                = $latestStat->getTrafficLeftReadable(4);
            $chdata['bonusPayload']['current_transfer_rate_left']
                = $latestStat->getTransferRateLeftReadable(4);
            $chdata['bonusPayload']['current_transfer_rate_left_midnight']
                = $latestStat->getTransferRateLeftReadable(4, TransmissionSettings::TARGET_SPEED_FRAME_DAY);
            $chdata['bonusPayload']['current_transfer_rate']
                = $latestStat->getTotalSpeedFromReferencePointReadable();
            $chdata['bonusPayload']['current_billing_frame_end']
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
        $chdata['bonusPayload']['current_throttling'] = $throttling;

        return $chdata;
    }

    public function getDynacard(string $property, string $locale): DynamicCard
    {
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
                $headerValue = $networkStatistics->getTrafficLeftReadable(4);
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

    public function getMobileSignalInfo($router = null): MobileSignalInfo
    {
        $info = new MobileSignalInfo($this->simpleCacheService);
        $info->fetchedAt = new DateTime('now');
        $type = $this->networkUsageProviderSettings->getProviderType();
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            try {
                if (false === ($router instanceof Router)) {
                    $router = new Router();
                    $router->setAddress($this->networkUsageProviderSettings->getAddress());
                    $router->login('admin', $this->networkUsageProviderSettings->getPassword());
                }
            } catch (\Exception $e) {
                $info->error = $e->getMessage();
                $info->errorOn = 'login';
                return $info;
            }

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
                $info->band = (int)$signal->band;
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

    private function getCurrentStatisticFromHuawei(): NetworkStatistic|null
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

    private function getTimeFrame(
        DateTime $frameStart,
        DateTime $frameEnd,
        int $frameDataLimit
    ): NetworkStatisticTimeFrame {
        $timeFrame = $this->networkStatisticTimeFrameRepository->findOneBy([
            'billingFrameStart' => $frameStart,
            'billingFrameEnd' => $frameEnd
        ]);
        if (!($timeFrame instanceof NetworkStatisticTimeFrame)) {
            $timeFrame = new NetworkStatisticTimeFrame();
        }
        $timeFrame->setBillingFrameDataLimit($frameDataLimit);
        $timeFrame->setBillingFrameStart($frameStart);
        $timeFrame->setBillingFrameEnd($frameEnd);
        $this->em->persist($timeFrame);
        return $timeFrame;
    }
}
