<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\NetworkMachine;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NetworkMachineRepository;

class DashboardService
{
    private EntityManagerInterface $em;

    private NetworkUsageService $networkUsageService;

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
            ($networkUsageSettings->getShowOnDashboard() === SimpleSettingsService::UNIVERSAL_TRUTH);
        if ($showNetworkUsageOnDashboard) {
            $dashboardData['network_statistic'] = $this->networkUsageService->getLatestStatistic();
        }

        return $dashboardData;
    }

    private function getNetworkMachineRepository(): NetworkMachineRepository
    {
        return $this->em->getRepository(NetworkMachine::class);
    }
}
