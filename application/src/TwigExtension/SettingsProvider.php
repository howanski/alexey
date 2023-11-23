<?php

declare(strict_types=1);

namespace App\TwigExtension;

use App\Service\NetworkUsageProviderSettings;
use App\Service\NetworkUsageService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SettingsProvider extends AbstractExtension
{
    public function __construct(
        private NetworkUsageProviderSettings $networkUsageProviderSettings,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isMikrotikRouterInUse', [$this, 'isMikrotikRouterInUse']),
        ];
    }

    public function isMikrotikRouterInUse(): bool
    {
        return
        NetworkUsageService::NETWORK_USAGE_PROVIDER_ROUTER_OS
        ===
        $this->networkUsageProviderSettings->getProviderType();
    }
}
