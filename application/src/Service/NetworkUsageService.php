<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use DateInterval;
use SimpleXMLElement;
use App\Form\NetworkChartType;
use App\Entity\NetworkStatistic;
use if0xx\HuaweiHilinkApi\Router;
use App\Class\TransmissionSettings;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\NetworkStatisticTimeFrame;
use App\Class\NetworkUsageProviderSettings;
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;

final class NetworkUsageService
{
    public const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';
    public const NETWORK_USAGE_PROVIDER_NONE = 'NONE';

    public function __construct(
        private EntityManagerInterface $em,
        private SimpleSettingsService $simpleSettingsService,
        private NetworkStatisticTimeFrameRepository $networkStatisticTimeFrameRepository,
        private NetworkStatisticRepository $networkStatisticRepository,
        private AlexeyTranslator $translator,
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
        $obsoleteStats = $this->networkStatisticRepository->findObsolete();
        foreach ($obsoleteStats as $stat) {
            $this->em->remove($stat);
        }
        $this->em->flush();
    }

    public function getCurrentStatistic()
    {
        $connectionSettings = $this->getConnectionSettings();
        $type = $connectionSettings->getProviderType();
        $stat = null;
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            $stat = $this->getCurrentStatisticFromHuawei($connectionSettings);
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

    public function getConnectionSettings(): NetworkUsageProviderSettings
    {
        $networkSettings = new NetworkUsageProviderSettings();
        $networkSettings->selfConfigure($this->simpleSettingsService);
        return $networkSettings;
    }

    public function saveConnectionSettings(NetworkUsageProviderSettings $settings): void
    {
        $settings->selfPersist($this->simpleSettingsService);
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


        $latestStat = $this->getLatestStatistic();
        if ($latestStat instanceof NetworkStatistic) {
            $chdata['bonusPayload']['current_traffic_left']
                = $latestStat->getTrafficLeftReadable(4);
            $chdata['bonusPayload']['current_transfer_rate_left']
                = $latestStat->getTransferRateLeftReadable(4);
            $chdata['bonusPayload']['current_transfer_rate']
                = $latestStat->getTotalSpeedFromReferencePointReadable();
            $chdata['bonusPayload']['current_billing_frame_end']
                = $latestStat->getTimeFrame()->getBillingFrameEndReadable($locale);
        } else {
            $chdata['bonusPayload']['current_traffic_left'] = 0;
            $chdata['bonusPayload']['current_transfer_rate_left'] = 0;
            $chdata['bonusPayload']['current_transfer_rate'] = 0;
            $chdata['bonusPayload']['current_billing_frame_end'] = 0;
        }

        try {
            $transmissionSettings = new TransmissionSettings();
            $transmissionSettings->selfConfigure($this->simpleSettingsService);
            $stat = $this->getLatestStatistic();
            $throttling = ($stat instanceof NetworkStatistic) ? $transmissionSettings->getProposedThrottleSpeed(
                speedLeft: $this->getLatestStatistic()->getTransferRateLeft()
            ) : 0;
            $throttling .= ' kB/s';
        } catch (\Exception $e) {
            $throttling = 'N. A.';
        }
        $chdata['bonusPayload']['current_throttling'] = $throttling;

        return $chdata;
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
        $now = new DateTime('now');
        $networkStatistics = $this->getPreparedEntitiesForChart($dateFrom, $now);

        /**
         * @var NetworkStatistic $stat
         */
        foreach ($networkStatistics as $stat) {
            $labels[] = $stat->getProbingTime()->format($timeFormat);
            $datasets['speed_relative']['data'][] = round(($stat->getTotalSpeedFromReferencePoint() / 1024), 4);
            $datasets['speed_left']['data'][] = round(($stat->getTransferRateLeft() / 1024), 4);
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

    private function getCurrentStatisticFromHuawei(NetworkUsageProviderSettings $connectionSettings)
    {
        try {
            $huaweiRouter = new Router();
            $huaweiRouter->setAddress($connectionSettings->getAddress());
            $huaweiRouter->login('admin', $connectionSettings->getPassword());

            /** @var SimpleXMLElement $monthStats */
            $monthStats = $huaweiRouter->getMonthStats();

            /** @var SimpleXMLElement $startDate */
            $startDate = $huaweiRouter->generalizedGet('api/monitoring/start_date');

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
