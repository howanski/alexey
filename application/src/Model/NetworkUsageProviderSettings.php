<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\SimpleSettingsService;

final class NetworkUsageProviderSettings
{
    private const PROVIDER_TYPE = 'NETWORK_USAGE_PROVIDER_TYPE';
    private const PROVIDER_ADDRESS = 'NETWORK_USAGE_PROVIDER_ADDRESS';
    private const PROVIDER_PASSWORD = 'NETWORK_USAGE_PROVIDER_PASSWORD';
    private const SHOW_ON_DASHBOARD = 'NETWORK_USAGE_SHOW_ON_DASHBOARD';

    private string $providerType;

    private string $address;

    private string $password;

    private string $showOnDashboard;

    public function selfConfigure(SimpleSettingsService $simpleSettingsService): void
    {
        $settingsArray = $simpleSettingsService->getSettings(
            keys: [
                self::PROVIDER_TYPE,
                self::PROVIDER_ADDRESS,
                self::PROVIDER_PASSWORD,
                self::SHOW_ON_DASHBOARD,
            ],
            user: null,
        );
        $this->setProviderType(strval($settingsArray[self::PROVIDER_TYPE]));
        $this->setAddress(strval($settingsArray[self::PROVIDER_ADDRESS]));
        $this->setPassword(strval($settingsArray[self::PROVIDER_PASSWORD]));
        $this->setShowOnDashboard(strval($settingsArray[self::SHOW_ON_DASHBOARD]));
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService)
    {
        $simpleSettingsService->saveSettings(
            settings: [
                self::PROVIDER_TYPE => $this->getProviderType(),
                self::PROVIDER_ADDRESS => $this->getAddress(),
                self::PROVIDER_PASSWORD => $this->getPassword(),
                self::SHOW_ON_DASHBOARD => $this->getShowOnDashboard(),
            ],
            user: null,
        );
    }

    public function getProviderType(): string
    {
        return $this->providerType;
    }

    public function setProviderType(string $providerType): self
    {
        $this->providerType = $providerType;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getShowOnDashboard(): string
    {
        return $this->showOnDashboard;
    }

    public function setShowOnDashboard(string $showOnDashboard): self
    {
        $this->showOnDashboard = $showOnDashboard;
        return $this;
    }
}
