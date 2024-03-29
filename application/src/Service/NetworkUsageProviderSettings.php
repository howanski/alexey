<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\SimpleSettingsService;

final class NetworkUsageProviderSettings
{
    private const PROVIDER_ADDRESS = 'NETWORK_USAGE_PROVIDER_ADDRESS';
    private const PROVIDER_BILLING_DAY = 'NETWORK_USAGE_PROVIDER_BILLING_DAY';
    private const PROVIDER_MONTHLY_LIMIT = 'NETWORK_USAGE_PROVIDER_MONTHLY_LIMIT';
    private const PROVIDER_PASSWORD = 'NETWORK_USAGE_PROVIDER_PASSWORD';
    private const PROVIDER_TYPE = 'NETWORK_USAGE_PROVIDER_TYPE';
    private const SHOW_ON_DASHBOARD = 'NETWORK_USAGE_SHOW_ON_DASHBOARD';

    private string $providerType;

    private string $address;

    private string $password;

    private string $showOnDashboard;

    private int $monthlyLimitGB;

    private int $billingDay;

    public function __construct(
        private SimpleSettingsService $simpleSettingsService
    ) {
        $this->selfConfigure();
    }

    private function selfConfigure(): void
    {
        $settingsArray = $this->simpleSettingsService->getSettings(
            keys: [
                self::PROVIDER_TYPE,
                self::PROVIDER_ADDRESS,
                self::PROVIDER_PASSWORD,
                self::SHOW_ON_DASHBOARD,
                self::PROVIDER_BILLING_DAY,
                self::PROVIDER_MONTHLY_LIMIT,
            ],
            user: null,
        );
        $this->setProviderType(strval($settingsArray[self::PROVIDER_TYPE]));
        $this->setAddress(strval($settingsArray[self::PROVIDER_ADDRESS]));
        $this->setPassword(strval($settingsArray[self::PROVIDER_PASSWORD]));
        $this->setShowOnDashboard(strval($settingsArray[self::SHOW_ON_DASHBOARD]));
        $this->setMonthlyLimitGB(intval($settingsArray[self::PROVIDER_MONTHLY_LIMIT]));
        $this->setBillingDay(intval($settingsArray[self::PROVIDER_BILLING_DAY]));
    }

    public function selfPersist()
    {
        $this->simpleSettingsService->saveSettings(
            settings: [
                self::PROVIDER_TYPE => $this->getProviderType(),
                self::PROVIDER_ADDRESS => $this->getAddress(),
                self::PROVIDER_PASSWORD => $this->getPassword(),
                self::SHOW_ON_DASHBOARD => $this->getShowOnDashboard(),
                self::PROVIDER_MONTHLY_LIMIT => strval($this->getMonthlyLimitGB()),
                self::PROVIDER_BILLING_DAY => strval($this->getBillingDay()),
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

    public function setMonthlyLimitGB(int $limit): self
    {
        $this->monthlyLimitGB = $limit;
        return $this;
    }

    public function getMonthlyLimitGB(): int
    {
        return $this->monthlyLimitGB;
    }

    public function setBillingDay(int $day): self
    {
        $this->billingDay = $day;
        return $this;
    }

    public function getBillingDay(): int
    {
        return $this->billingDay;
    }
}
