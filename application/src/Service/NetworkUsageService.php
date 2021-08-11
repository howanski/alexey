<?php

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
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;

class NetworkUsageService
{
    public const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';
    public const NETWORK_USAGE_PROVIDER_NONE = 'NONE';

    public const DASHBOARD_SHOW = 'SHOW';
    public const DASHBOARD_HIDE = 'HIDE';

    public const CHART_TYPE_SPEED_TODAY = 'speed-today';

    private const PROVIDER_TYPE = 'NETWORK_USAGE_PROVIDER_TYPE';
    private const PROVIDER_ADDRESS = 'NETWORK_USAGE_PROVIDER_ADDRESS';
    private const PROVIDER_PASSWORD = 'NETWORK_USAGE_PROVIDER_PASSWORD';
    private const SHOW_ON_DASHBOARD = 'NETWORK_USAGE_SHOW_ON_DASHBOARD';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SimpleSettingsService
     */
    private $simpleSettingsService;

    /**
     * @var NetworkStatisticTimeFrameRepository
     */
    private $networkStatisticTimeFrameRepository;

    /**
     * @var NetworkStatisticRepository
     */
    private $networkStatisticRepository;

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

    public function getCurrentStatistic($alsoSave = false)
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
        if ($alsoSave && !empty($stat)) {
            $this->em->persist($stat);
            $this->em->flush();
        }
        return $stat;
    }

    public function getConnectionSettings(): NetworkUsageProviderSettings
    {
        $networkSettings = new NetworkUsageProviderSettings();
        $settingsArray = $this->simpleSettingsService->getSettings([
            self::PROVIDER_TYPE,
            self::PROVIDER_ADDRESS,
            self::PROVIDER_PASSWORD,
            self::SHOW_ON_DASHBOARD,
        ]);
        $networkSettings->setProviderType(strval($settingsArray[self::PROVIDER_TYPE]));
        $networkSettings->setAddress(strval($settingsArray[self::PROVIDER_ADDRESS]));
        $networkSettings->setPassword(strval($settingsArray[self::PROVIDER_PASSWORD]));
        $networkSettings->setShowOnDashboard(strval($settingsArray[self::SHOW_ON_DASHBOARD]));
        return $networkSettings;
    }

    public function saveConnectionSettings(NetworkUsageProviderSettings $settings)
    {
        $this->simpleSettingsService->saveSettings([
            self::PROVIDER_TYPE => $settings->getProviderType(),
            self::PROVIDER_ADDRESS => $settings->getAddress(),
            self::PROVIDER_PASSWORD => $settings->getPassword(),
            self::SHOW_ON_DASHBOARD => $settings->getShowOnDashboard(),
        ]);
    }

    public function getDataForChart($chartDataType): array
    {
        $labels = [];
        $datasets = [];
        if ($chartDataType === self::CHART_TYPE_SPEED_TODAY) {
            $today = new DateTime('today');
            $chdata = $this->prepareDataForChart($today, 'speed');
            $labels = $chdata['labels'];
            $datasets = $chdata['datasets'];
        }
        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

    private function prepareDataForChart(DateTimeInterface $dateFrom, string $dataType): array
    {
        $data = [];
        $labels = [];
        $datasets = [];
        $datasets[0] = [];
        $now = new DateTime('now');
        $networkStatistics = $this->getPreparedEntitiesForChart($dateFrom, $now);

        /**
         * @var NetworkStatistic $stat
         */
        foreach ($networkStatistics as $stat) {
            $labels[] = $stat->getProbingTime()->format('H:i:s');
            if ('speed' == $dataType) {
                $datasets[0][] = (int)($stat->getTotalSpeedFromReferencePoint() / 1024);
            } else {
                $datasets[0][] = 0;
            }
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
        array_shift($networkStatistics); //first one will have bad statistics //TODO: also breaking points
        return $networkStatistics;
    }

    private function getCurrentStatisticFromHuawei(NetworkUsageProviderSettings $connectionSettings): NetworkStatistic
    {
        $huaweiRouter = new Router();
        $huaweiRouter->setAddress($connectionSettings->getAddress());
        $huaweiRouter->login('admin', $connectionSettings->getPassword());

        /**
         * @var SimpleXMLElement $monthStats
         */
        $monthStats = $huaweiRouter->getMonthStats();

        /**
         * @var SimpleXMLElement $monthStats
         */
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

    private function getTimeFrame(DateTimeInterface $frameStart, DateTimeInterface $frameEnd, int $frameDataLimit)
    {
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
