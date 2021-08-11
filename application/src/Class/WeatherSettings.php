<?php

namespace App\Class;

class WeatherSettings
{
    public const DASHBOARD_SHOW = 'SHOW';
    public const DASHBOARD_HIDE = 'HIDE';

    /**
     * @var string
     */
    private $latitude;

    /**
     * @var string
     */
    private $longitude;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $showOnDashboard;

    /**
     * @return  string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param  string  $latitude
     * @return  self
     */
    public function setLatitude(string $latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return  string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @param  string  $longitude
     * @return  self
     */
    public function setLongitude(string $longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return  string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param  string  $apiKey
     * @return  self
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return  string
     */
    public function getShowOnDashboard(): string
    {
        return $this->showOnDashboard;
    }

    /**
     * @param  string  $showOnDashboard
     * @return  self
     */
    public function setShowOnDashboard(string $showOnDashboard)
    {
        $this->showOnDashboard = $showOnDashboard;
        return $this;
    }
}
