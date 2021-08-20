<?php

declare(strict_types=1);

namespace App\Class;

class WeatherSettings
{
    public const DASHBOARD_SHOW = 'SHOW';
    public const DASHBOARD_HIDE = 'HIDE';

    private string $latitude;

    private string $longitude;

    private string $apiKey;

    private string $showOnDashboard;

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
