<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AssistantCall;
use App\Entity\NetworkMachine;
use App\Model\AssistantSettings;
use App\Repository\AssistantCallRepository;
use App\Repository\NetworkMachineRepository;
use App\Service\NetworkUsageProviderSettings;
use App\Service\SimpleSettingsService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class DashboardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NetworkUsageProviderSettings $networkUsageProviderSettings,
        private SimpleSettingsService $simpleSettingsService,
        private WeatherService $weatherService,
    ) {
    }

    public function getDashboardData(?UserInterface $user = null): array
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

        $dashboardData['assistant'] = $this->getAssistantDashboardIds($user);

        return $dashboardData;
    }

    private function getAssistantDashboardIds(?UserInterface $user = null): ?array
    {
        $settings = $this->simpleSettingsService->getSettings([AssistantSettings::SHOW_ON_DASHBOARD], $user);
        if (
            is_array($settings)
            && array_key_exists(AssistantSettings::SHOW_ON_DASHBOARD, $settings)
            && $settings[AssistantSettings::SHOW_ON_DASHBOARD] === SimpleSettingsService::UNIVERSAL_TRUTH
        ) {
            /** @var AssistantCallRepository */
            $repo = $this->em->getRepository(AssistantCall::class);

            $ids = $repo->getUnreadChatsIds($user);
            if (empty($ids)) {
                return null;
            }
            return $ids;
        }
        return null;
    }

    private function getNetworkMachineRepository(): NetworkMachineRepository
    {
        /** @var NetworkMachineRepository */
        $repo = $this->em->getRepository(NetworkMachine::class);
        return $repo;
    }
}
