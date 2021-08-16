<?php

namespace App\Service;

use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use App\Class\WeatherSettings;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private const LATITUDE = 'WEATHER_LAT';
    private const LONGITUDE = 'WEATHER_LON';
    private const API_KEY = 'WEATHER_API_KEY';
    private const SHOW_ON_DASHBOARD = 'WEATHER_SHOW_ON_DASHBOARD';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var SimpleSettingsService
     */
    private $simpleSettingsService;

    public function __construct(HttpClientInterface $client, SimpleSettingsService $simpleSettingsService)
    {
        $this->client = $client;
        $this->simpleSettingsService = $simpleSettingsService;
    }

    public function getCurrentWeather(): OpenWeatherOneApiResponse
    {
        return $this->prepareWeatherObject();
    }

    public function setWeatherSettings(WeatherSettings $settings)
    {
        $this->simpleSettingsService->saveSettings([
            self::LATITUDE => $settings->getLatitude(),
            self::LONGITUDE => $settings->getLongitude(),
            self::API_KEY => $settings->getApiKey(),
            self::SHOW_ON_DASHBOARD => $settings->getShowOnDashboard(),
        ]);
    }

    public function getWeatherSettings(): WeatherSettings
    {
        $arraySettings = $this->simpleSettingsService->getSettings([
            self::LATITUDE,
            self::LONGITUDE,
            self::API_KEY,
            self::SHOW_ON_DASHBOARD,
        ]);
        $settings = new WeatherSettings();
        $settings->setLatitude(strval($arraySettings[self::LATITUDE]));
        $settings->setLongitude(strval($arraySettings[self::LONGITUDE]));
        $settings->setApiKey(strval($arraySettings[self::API_KEY]));
        $settings->setShowOnDashboard(strval($arraySettings[self::SHOW_ON_DASHBOARD]));
        return $settings;
    }

    private function prepareWeatherObject(): OpenWeatherOneApiResponse
    {
        $weather = new OpenWeatherOneApiResponse($this->client, $this->getWeatherSettings());

        return $weather;
    }

    public function getApiDocumentation(): string
    {
        return 'https://openweathermap.org/api/one-call-api';
    }
}
