<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\NetworkMachine;
use App\Service\WeatherService;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NetworkMachineRepository;

final class DashboardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NetworkUsageService $networkUsageService,
        private WeatherService $weatherService,
    ) {
    }

    public function getDashboardData(): array
    {
        $dashboardData = [];

        $networkMachineRepository = $this->getNetworkMachineRepository();
        $dashboardData['machines'] = $networkMachineRepository->findBy(['showOnDashboard' => true]);


        $networkUsageSettings = $this->networkUsageService->getConnectionSettings();
        $showNetworkUsageOnDashboard =
            ($networkUsageSettings->getShowOnDashboard() === SimpleSettingsService::UNIVERSAL_TRUTH);
        if (true === $showNetworkUsageOnDashboard) {
            $dashboardData['network_statistic'] = true;
        }

        if ($this->weatherService->showWeatherOnDashboard()) {
            $dashboardData['weather'] = $this->weatherService->getWeather();
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
