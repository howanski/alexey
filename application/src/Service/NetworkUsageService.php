<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use DateInterval;
use SimpleXMLElement;
use DateTimeInterface;
use App\Entity\NetworkStatistic;
use if0xx\HuaweiHilinkApi\Router;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\NetworkStatisticTimeFrame;
use App\Class\NetworkUsageProviderSettings;
use App\Form\NetworkChartType;
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;

class NetworkUsageService
{
    public const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';
    public const NETWORK_USAGE_PROVIDER_NONE = 'NONE';

    private EntityManagerInterface $em;

    private SimpleSettingsService $simpleSettingsService;

    private NetworkStatisticTimeFrameRepository $networkStatisticTimeFrameRepository;

    private NetworkStatisticRepository $networkStatisticRepository;

    public function __construct(
        EntityManagerInterface $em,
        SimpleSettingsService $simpleSettingsService,
        NetworkStatisticTimeFrameRepository $networkStatisticTimeFrameRepository,
        NetworkStatisticRepository $networkStatisticRepository
    ) {
        $this->em = $em;
        $this->simpleSettingsService = $simpleSettingsService;
        $this->networkStatisticTimeFrameRepository = $networkStatisticTimeFrameRepository;
        $this->networkStatisticRepository = $networkStatisticRepository;
    }

    public function getCurrentStatistic(): NetworkStatistic
    {
        $connectionSettings = $this->getConnectionSettings();
        $type = $connectionSettings->getProviderType();
        $stat = null;
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            $stat = $this->getCurrentStatisticFromHuawei($connectionSettings);
        } elseif (empty($type) || $type === self::NETWORK_USAGE_PROVIDER_NONE) {
            // No settings, no work, great!
        } else {
            throw new \Exception('Unknown network usage provider.');
        }
        return $stat;
    }

    public function getLatestStatistic(): NetworkStatistic
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

    public function getDataForChart($chartDataType): array
    {
        $chdata = [
            'labels' => [],
            'datasets' => []
        ];
        $today = new DateTime('today');
        $now = new DateTime('now');
        if ($chartDataType == NetworkChartType::CHART_TYPE_TODAY) {
            $chdata = $this->prepareDataForChart($today);
        } elseif ($chartDataType == NetworkChartType::CHART_TYPE_WEEK) {
            $shift = new DateInterval('P1W');
            $today->sub($shift);
            $chdata = $this->prepareDataForChart($today);
        } elseif ($chartDataType == NetworkChartType::CHART_TYPE_MONTH) {
            $shift = new DateInterval('P1M');
            $today->sub($shift);
            $chdata = $this->prepareDataForChart($today);
        } elseif ($chartDataType == NetworkChartType::CHART_TYPE_HOURS_TWO) {
            $shift = new DateInterval('PT2H');
            $now->sub($shift);
            $chdata = $this->prepareDataForChart($now);
        } elseif ($chartDataType == NetworkChartType::CHART_TYPE_MINUTES_TEN) {
            $shift = new DateInterval('PT10M');
            $now->sub($shift);
            $chdata = $this->prepareDataForChart($now);
        } elseif ($chartDataType == NetworkChartType::CHART_TYPE_BILLING_FRAME) {
            $currentStat = $this->getLatestStatistic();
            $billingStart = $currentStat->getTimeFrame()->getBillingFrameStart();
            $chdata = $this->prepareDataForChart($billingStart);
        }
        $latestStat = $this->getLatestStatistic();
        if ($latestStat instanceof NetworkStatistic) {
            $current = [
                'current_traffic_left' => $latestStat->getTrafficLeftReadable(4),
                'current_transfer_rate_left' => $latestStat->getTransferRateLeftReadable(4),
                'current_transfer_rate' => $latestStat->getTotalSpeedFromReferencePointReadable(),
                'current_billing_frame_end' => $latestStat->getTimeFrame()->getBillingFrameEndReadable(),
            ];
        } else {
            $current = [
                'current_traffic_left' => 0,
                'current_transfer_rate_left' => 0,
                'current_transfer_rate' => 0,
                'current_billing_frame_end' => 0,
            ];
        }

        return [
            'labels' => $chdata['labels'],
            'datasets' => $chdata['datasets'],
            'current' => $current
        ];
    }

    private function prepareDataForChart(DateTimeInterface $dateFrom): array
    {
        $data = [];
        $labels = [];
        $datasets = [];
        $datasets['speed_relative'] = [
            'label' => "Traffic rate (kB/s)",
            'lineTension' => 0.3,
            'backgroundColor' => "rgba(78, 115, 223, 0.05)",
            'borderColor' => "rgba(78, 115, 223, 1)",
            'pointRadius' => 3,
            'pointBackgroundColor' => "rgba(78, 115, 223, 1)",
            'pointBorderColor' => "rgba(78, 115, 223, 1)",
            'pointHoverRadius' => 3,
            'pointHoverBackgroundColor' => "rgba(78, 115, 223, 1)",
            'pointHoverBorderColor' => "rgba(78, 115, 223, 1)",
            'pointHitRadius' => 10,
            'pointBorderWidth' => 2,
            'data' => [],
        ];
        $datasets['speed_left'] = [
            'label' => "Traffic left (kB/s)",
            'lineTension' => 0.3,
            'backgroundColor' => "rgba(78, 222, 223, 0.05)",
            'borderColor' => "rgba(78, 222, 223, 1)",
            'pointRadius' => 3,
            'pointBackgroundColor' => "rgba(78, 222, 223, 1)",
            'pointBorderColor' => "rgba(78, 222, 223, 1)",
            'pointHoverRadius' => 3,
            'pointHoverBackgroundColor' => "rgba(78, 222, 223, 1)",
            'pointHoverBorderColor' => "rgba(78, 222, 223, 1)",
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
            $labels[] = $stat->getProbingTime()->format('d.m H:i');
            $datasets['speed_relative']['data'][] = round(($stat->getTotalSpeedFromReferencePoint() / 1024), 4);
            $datasets['speed_left']['data'][] = round(($stat->getTransferRateLeft() / 1024), 4);
        }
        $data['labels'] = $labels;
        $data['datasets'] = $datasets;
        return $data;
    }

    private function getPreparedEntitiesForChart(DateTime $dateFrom, DateTime $dateTo, int $maxRecords = 50): array
    {
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
        foreach ($networkStatistics as $key => $stat) {
            if (isset($networkStatistics[$key + 1])) {
                if ($stat->getTimeFrame() == $networkStatistics[$key + 1]->getTimeFrame()) {
                    $networkStatistics[$key + 1]->setReferencePoint($stat);
                }
            }
        }
        array_shift($networkStatistics); //first one can have bad statistics
        return $networkStatistics;
    }

    private function getCurrentStatisticFromHuawei(NetworkUsageProviderSettings $connectionSettings): NetworkStatistic
    {
        $huaweiRouter = new Router();
        $huaweiRouter->setAddress($connectionSettings->getAddress());
        $huaweiRouter->login('admin', $connectionSettings->getPassword());

        $monthStats = $huaweiRouter->getMonthStats();

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
    }

    private function getTimeFrame(
        DateTimeInterface $frameStart,
        DateTimeInterface $frameEnd,
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
