<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use App\Class\WeatherSettings;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    public function __construct(
        private HttpClientInterface $client,
        private SimpleSettingsService $simpleSettingsService,
        private SimpleCacheService $simpleCacheService
    ) {
    }

    public function getWeather(): OpenWeatherOneApiResponse
    {
        return $this->prepareWeatherObject();
    }

    public function showWeatherOnDashboard(): bool
    {
        $settings = $this->getWeatherSettings();
        return (SimpleSettingsService::UNIVERSAL_TRUTH == $settings->getShowOnDashboard());
    }

    private function prepareWeatherObject(): OpenWeatherOneApiResponse
    {
        $weather = new OpenWeatherOneApiResponse($this->client, $this->getWeatherSettings(), $this->simpleCacheService);

        return $weather;
    }

    private function getWeatherSettings(): WeatherSettings
    {
        $settings = new WeatherSettings();
        $settings->selfConfigure($this->simpleSettingsService);
        return $settings;
    }

    public function getApiDocumentation(): string
    {
        return 'https://openweathermap.org/api/one-call-api';
    }
}
