<?php

declare(strict_types=1);

namespace App\Class;

use App\Service\SimpleSettingsService;

class WeatherSettings
{
    private const LATITUDE = 'WEATHER_LAT';
    private const LONGITUDE = 'WEATHER_LON';
    private const API_KEY = 'WEATHER_API_KEY';
    private const SHOW_ON_DASHBOARD = 'WEATHER_SHOW_ON_DASHBOARD';

    private string $latitude;

    private string $longitude;

    private string $apiKey;

    private string $showOnDashboard;

    public function selfConfigure(SimpleSettingsService $simpleSettingsService): void
    {
        $arraySettings = $simpleSettingsService->getSettings(
            keys: [
                self::LATITUDE,
                self::LONGITUDE,
                self::API_KEY,
                self::SHOW_ON_DASHBOARD,
            ],
            user: null,
        );
        $this->setLatitude(strval($arraySettings[self::LATITUDE]));
        $this->setLongitude(strval($arraySettings[self::LONGITUDE]));
        $this->setApiKey(strval($arraySettings[self::API_KEY]));
        $this->setShowOnDashboard(strval($arraySettings[self::SHOW_ON_DASHBOARD]));
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService): void
    {
        $simpleSettingsService->saveSettings(
            settings: [
                self::LATITUDE => $this->getLatitude(),
                self::LONGITUDE => $this->getLongitude(),
                self::API_KEY => $this->getApiKey(),
                self::SHOW_ON_DASHBOARD => $this->getShowOnDashboard(),
            ],
            user: null,
        );
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
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
