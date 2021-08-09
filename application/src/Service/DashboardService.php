<?php

namespace App\Service;

use App\Entity\NetworkMachine;
use App\Repository\NetworkMachineRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var NetworkUsageService
     */
    private $networkUsageService;

    public function __construct(EntityManagerInterface $em, NetworkUsageService $networkUsageService)
    {
        $this->em = $em;
        $this->networkUsageService = $networkUsageService;
    }

    public function getDashboardData(): array
    {
        $dashboardData = [];

        $dashboardData['refresh_time'] = 30000;

        $networkMachineRepository = $this->getNetworkMachineRepository();
        $dashboardData['machines'] = $networkMachineRepository->findBy(['showOnDashboard' => true]);


        $networkUsageSettings = $this->networkUsageService->getConnectionSettings();
        $showNetworkUsageOnDashboard =
            ($networkUsageSettings->getShowOnDashboard() === NetworkUsageService::DASHBOARD_SHOW);
        if ($showNetworkUsageOnDashboard) {
            try {
                $dashboardData['network_statistic'] = $this->networkUsageService->getCurrentStatistic(false);
            } catch (\Exception $e) {
                //TODO: For now ignored, but test would be useful
            }
        }

        return $dashboardData;
    }

    private function getNetworkMachineRepository(): NetworkMachineRepository
    {
        return $this->em->getRepository(NetworkMachine::class);
    }
}
