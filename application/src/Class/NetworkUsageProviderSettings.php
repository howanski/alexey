<?php

namespace App\Class;

use App\Service\SimpleSettingsService;

class NetworkUsageProviderSettings
{
    private const PROVIDER_TYPE = 'NETWORK_USAGE_PROVIDER_TYPE';
    private const PROVIDER_ADDRESS = 'NETWORK_USAGE_PROVIDER_ADDRESS';
    private const PROVIDER_PASSWORD = 'NETWORK_USAGE_PROVIDER_PASSWORD';
    private const SHOW_ON_DASHBOARD = 'NETWORK_USAGE_SHOW_ON_DASHBOARD';

    /**
     * @var string
     */
    private $providerType;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $showOnDashboard;

    public function selfConfigure(SimpleSettingsService $simpleSettingsService)
    {
        $settingsArray = $simpleSettingsService->getSettings([
            self::PROVIDER_TYPE,
            self::PROVIDER_ADDRESS,
            self::PROVIDER_PASSWORD,
            self::SHOW_ON_DASHBOARD,
        ]);
        $this->setProviderType(strval($settingsArray[self::PROVIDER_TYPE]));
        $this->setAddress(strval($settingsArray[self::PROVIDER_ADDRESS]));
        $this->setPassword(strval($settingsArray[self::PROVIDER_PASSWORD]));
        $this->setShowOnDashboard(strval($settingsArray[self::SHOW_ON_DASHBOARD]));
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService)
    {
        $simpleSettingsService->saveSettings([
            self::PROVIDER_TYPE => $this->getProviderType(),
            self::PROVIDER_ADDRESS => $this->getAddress(),
            self::PROVIDER_PASSWORD => $this->getPassword(),
            self::SHOW_ON_DASHBOARD => $this->getShowOnDashboard(),
        ]);
    }

    /**
     * @return  string
     */
    public function getProviderType()
    {
        return $this->providerType;
    }

    /**
     * @param  string  $providerType
     *
     * @return  self
     */
    public function setProviderType(string $providerType)
    {
        $this->providerType = $providerType;

        return $this;
    }

    /**
     * @return  string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param  string  $address
     *
     * @return  self
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return  string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param  string  $password
     *
     * @return  self
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return  string
     */
    public function getShowOnDashboard()
    {
        return $this->showOnDashboard;
    }

    /**
     * @param  string  $showOnDashboard
     *
     * @return  self
     */
    public function setShowOnDashboard(string $showOnDashboard)
    {
        $this->showOnDashboard = $showOnDashboard;

        return $this;
    }
}
