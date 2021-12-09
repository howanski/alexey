<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\NetworkMachine;
use App\Repository\NetworkMachineRepository;
use App\Service\NetworkUsageProviderSettings;
use App\Service\SimpleSettingsService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

final class DashboardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private WeatherService $weatherService,
        private NetworkUsageProviderSettings $networkUsageProviderSettings,
    ) {
    }

    public function getDashboardData(): array
    {
        $dashboardData = [];

        $networkMachineRepository = $this->getNetworkMachineRepository();
        $dashboardData['machines'] = $networkMachineRepository->findBy(['showOnDashboard' => true]);

        $showNetworkUsageOnDashboard =
            ($this->networkUsageProviderSettings->getShowOnDashboard() === SimpleSettingsService::UNIVERSAL_TRUTH);
        if (true === $showNetworkUsageOnDashboard) {
            $dashboardData['network_statistic'] = true;
        }

        if ($this->weatherService->showWeatherOnDashboard()) {
            $dashboardData['weather'] = true;
        }

        return $dashboardData;
    }

    private function getNetworkMachineRepository(): NetworkMachineRepository
    {
        /** @var NetworkMachineRepository */
        $repo = $this->em->getRepository(NetworkMachine::class);
        return $repo;
    }
}
