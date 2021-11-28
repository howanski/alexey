<?php

declare(strict_types=1);

namespace App\Class;

use App\Model\WeatherSettings;
use App\Service\SimpleCacheService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenWeatherOneApiResponse
{
    // TODO: use key with user id or locale so they won't mix
    public const WEATHER_CACHE_KEY = 'WEATHER';

    # https://openweathermap.org/api/one-call-api
    private array $rawApiResponse;

    private HttpClientInterface $client;

    private SimpleCacheService $simpleCacheService;

    private string $latitude;

    private string $longitude;

    private string $apiKey;

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

    private function ensureWeatherDataFetched(string $locale): void
    {
        $cachedResponse = $this->simpleCacheService->retrieveDataFromCache(key: self::WEATHER_CACHE_KEY);
        if (count($cachedResponse) === 0) {
            $this->fetchWeatherFromServer(locale: $locale);
            $this->storeWeatherDataInCache();
        } else {
            $this->rawApiResponse = $cachedResponse;
        }
    }

    private function fetchWeatherFromServer(string $locale): void
    {
        $response = $this->client->request(
            'GET',
            'https://api.openweathermap.org/data/2.5/onecall?lat=' . $this->latitude .
                '&lon=' . $this->longitude .
                '&exclude=minutely&units=metric&lang=' . $locale . '&appid=' . $this->apiKey
        );
        $this->rawApiResponse = $response->toArray();
    }

    private function storeWeatherDataInCache()
    {
        $validTo = new \DateTime('now');
        $interval = new \DateInterval('PT3H');
        $validTo->add($interval);
        $this->simpleCacheService->cacheData(
            key: self::WEATHER_CACHE_KEY,
            data: $this->rawApiResponse,
            validTo: $validTo,
        );
    }

    public function getWeatherReadable($locale): array
    {
        try {
            $this->ensureWeatherDataFetched(locale: $locale);
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
                    'temperature_feels_like' => $hourly['feels_like'],
                    'time' => $time,
                    'weather' => $hourly['weather'][0]['description'],
                    'weather_icon' => $hourly['weather'][0]['icon'],
                    'rain' => array_key_exists('rain', $hourly) ? $hourly['rain']['1h'] : 0,
                    'snow' => array_key_exists('snow', $hourly) ? $hourly['snow']['1h'] : 0,
                    'wind_speed' => $hourly['wind_speed'],
                ];
            }
        }
        $today = new Carbon('today');
        foreach ($raw['daily'] as $daily) {
            $time = (new Carbon($daily['dt']))->setTimezone($timeZone);
            if ($time > $today) {
                $timeReadable = $time->format('D');
                $readable['daily'][] = [
                    'temperature' => $daily['temp']['min'] . ' - ' . $daily['temp']['max'],
                    'temperature_detailed' => $daily['temp'],
                    'temperature_feels_like' => $daily['feels_like'],
                    'date' => $timeReadable,
                    'weather' => $daily['weather'][0]['description'],
                    'weather_icon' => $daily['weather'][0]['icon'],
                    'rain' => array_key_exists('rain', $daily) ? $daily['rain'] : 0,
                    'snow' => array_key_exists('snow', $daily) ? $daily['snow'] : 0,
                    'wind_speed' => $daily['wind_speed'],
                ];
            }
        }
        return $readable;
    }
}
