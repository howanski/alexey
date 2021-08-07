<?php

namespace App\Service;

use DateTime;
use SimpleXMLElement;
use App\Entity\NetworkStatistic;
use if0xx\HuaweiHilinkApi\Router;
use Doctrine\ORM\EntityManagerInterface;
use App\Class\NetworkUsageProviderSettings;
use DateInterval;

class NetworkUsageService
{
    private const NETWORK_USAGE_PROVIDER_HUAWEI = 'HILINK';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getCurrentStatistic($alsoSave = false)
    {
        $connectionSettings = $this->getConnectionSettings();
        $type = $connectionSettings->getProviderType();
        $stat = null;
        if ($type === self::NETWORK_USAGE_PROVIDER_HUAWEI) {
            $stat = $this->getCurrentStatisticFromHuawei($connectionSettings);
        } elseif (empty($type)) {
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
        $stat->setBillingFrameDataLimit($trafficMaxLimit);
        $stat->setBillingFrameStart($monthStart);
        $stat->setBillingFrameEnd($monthEnd);

        return $stat;
    }

    private function getConnectionSettings(): NetworkUsageProviderSettings
    {
        $settings = new NetworkUsageProviderSettings();
        $settings->setProviderType(self::NETWORK_USAGE_PROVIDER_HUAWEI);
        $settings->setAddress('TODO');
        $settings->setPassword('TODO');
        return $settings;
    }
}
