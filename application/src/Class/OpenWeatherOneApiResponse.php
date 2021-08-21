<?php

declare(strict_types=1);

namespace App\Class;

use App\Service\SimpleCacheService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherOneApiResponse
{
    public const WEATHER_CACHE_KEY = 'WEATHER';

    private array $rawApiResponse;

    private HttpClientInterface $client;

    private SimpleCacheService $simpleCacheService;

    private string $latitude;

    private string $longitude;

    private string $apiKey;

    public function setRawApiResponse(array $rawApiResponse): self
    {
        $this->rawApiResponse = $rawApiResponse;
        return $this;
    }

    public function getRawApiResponse(): array
    {
        return $this->rawApiResponse;
    }

    public function __construct(
        HttpClientInterface $client,
        WeatherSettings $weatherSettings,
        SimpleCacheService $simpleCacheService
    ) {
        $this->client = $client;
        $this->latitude = $weatherSettings->getLatitude();
        $this->longitude = $weatherSettings->getLongitude();
        $this->apiKey = $weatherSettings->getApiKey();
        $this->simpleCacheService = $simpleCacheService;
    }

    private function ensureWeatherDataFetched(): void
    {
        if (empty($this->rawApiResponse)) {
            $cachedResponse = $this->simpleCacheService->retrieveDataFromCache(self::WEATHER_CACHE_KEY);
            if (empty($cachedResponse)) {
                $response = $this->client->request(
                    'GET',
                    'https://api.openweathermap.org/data/2.5/onecall?lat=' . $this->latitude .
                        '&lon=' . $this->longitude .
                        '&exclude=minutely&units=metric&lang=en&appid=' . $this->apiKey
                );
                $this->rawApiResponse = $response->toArray();
                $validTo = new \DateTime('now');
                $interval = new \DateInterval('PT3H');
                $validTo->add($interval);
                $this->simpleCacheService->cacheData(self::WEATHER_CACHE_KEY, $this->rawApiResponse, $validTo);
            } else {
                $this->rawApiResponse = $cachedResponse;
            }
        }
    }

    public function getWeatherReadable(): array
    {
        try {
            $this->ensureWeatherDataFetched();
        } catch (\Exception) {
            return [
                'hourly' => [],
                'daily' => [],
            ];
        }

        $timeZone = new CarbonTimeZone('Europe/Warsaw');
        $raw = $this->rawApiResponse;
        $readable = [
            'hourly' => [],
            'daily' => [],
        ];
        $now = new Carbon('now');
        foreach ($raw['hourly'] as $hourly) {
            $time = (new Carbon($hourly['dt']))->setTimezone($timeZone);
            if ($time > $now) {
                $readable['hourly'][] = [
                    'temperature' => $hourly['temp'],
                    'time' => $time,
                    'weather' => $hourly['weather'][0]['description'],
                    'weather_icon' => $hourly['weather'][0]['icon'],
                    'rain' => array_key_exists('rain', $hourly) ? $hourly['rain']['1h'] : 0,
                ];
            }
        }
        foreach ($raw['daily'] as $daily) {
            $today = new Carbon('today');
            $time = (new Carbon($daily['dt']))->setTimezone($timeZone);
            if ($time > $today) {
                $timeReadable = $time->format('D d.m');
                $readable['daily'][] = [
                    'temperature' => $daily['temp']['min'] . ' - ' . $daily['temp']['max'],
                    'date' => $timeReadable,
                    'weather' => $daily['weather'][0]['description'],
                    'weather_icon' => $daily['weather'][0]['icon'],
                    'rain' => array_key_exists('rain', $daily) ? $daily['rain'] : 0,
                ];
            }
        }
        return $readable;
    }
}
