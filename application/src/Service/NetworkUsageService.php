<?php

namespace App\Service;

use DateTime;
use DateInterval;
use SimpleXMLElement;
use App\Entity\NetworkStatistic;
use if0xx\HuaweiHilinkApi\Router;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Class\NetworkUsageProviderSettings;
use App\Entity\NetworkStatisticTimeFrame;
use App\Repository\NetworkStatisticTimeFrameRepository;
use DateTimeInterface;

class NetworkUsageService
{
    public const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';
    public const NETWORK_USAGE_PROVIDER_NONE = 'NONE';

    private const PROVIDER_TYPE = 'NETWORK_USAGE_PROVIDER_TYPE';
    private const PROVIDER_ADDRESS = 'NETWORK_USAGE_PROVIDER_ADDRESS';
    private const PROVIDER_PASSWORD = 'NETWORK_USAGE_PROVIDER_PASSWORD';

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

    public function __construct(
        EntityManagerInterface $em,
        SimpleSettingsService $simpleSettingsService,
        NetworkStatisticTimeFrameRepository $networkStatisticTimeFrameRepository
    ) {
        $this->em = $em;
        $this->simpleSettingsService = $simpleSettingsService;
        $this->networkStatisticTimeFrameRepository = $networkStatisticTimeFrameRepository;
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
            self::PROVIDER_PASSWORD
        ]);
        $networkSettings->setProviderType(strval($settingsArray[self::PROVIDER_TYPE]));
        $networkSettings->setAddress(strval($settingsArray[self::PROVIDER_ADDRESS]));
        $networkSettings->setPassword(strval($settingsArray[self::PROVIDER_PASSWORD]));
        return $networkSettings;
    }

    public function saveConnectionSettings(NetworkUsageProviderSettings $settings)
    {
        $this->simpleSettingsService->saveSettings([
            self::PROVIDER_TYPE => $settings->getProviderType(),
            self::PROVIDER_ADDRESS => $settings->getAddress(),
            self::PROVIDER_PASSWORD => $settings->getPassword()
        ]);
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