<?php

namespace App\Class;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use DateTime;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherOneApiResponse
{
    /**
     * @var array
     */
    private $rawApiResponse = [];

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var DateTime
     */
    private $now;

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

    public function setRawApiResponse(array $rawApiResponse): self
    {
        $this->rawApiResponse = $rawApiResponse;
        return $this;
    }

    public function __construct(HttpClientInterface $client, WeatherSettings $weatherSettings)
    {
        $this->client = $client;
        $this->latitude = $weatherSettings->getLatitude();
        $this->longitude = $weatherSettings->getLongitude();
        $this->apiKey = $weatherSettings->getApiKey();
    }

    private function ensureWeatherDataFetched()
    {
        if (empty($this->rawApiResponse)) {
            if (empty($this->client)) {
                throw new \Exception('http client not set');
            }
            $this->now = new DateTime('now');
            $response = $this->client->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/onecall?lat=' . $this->latitude .
                    '&lon=' . $this->longitude .
                    '&exclude=minutely&units=metric&lang=en&appid=' . $this->apiKey
            );

            $this->rawApiResponse = $response->toArray();
        }
    }

    public function getHourlyWeatherReadable()
    {
        $this->ensureWeatherDataFetched();
        $timeZone = new CarbonTimeZone('Europe/Warsaw');
        $raw = $this->rawApiResponse;
        $readable = [
            'hourly' => []
        ];
        foreach ($raw['hourly'] as $hourly) {
            $readable['hourly'][] = [
                'temperature' => $hourly['temp'],
                'time' => (new Carbon($hourly['dt']))->setTimezone($timeZone),
                'weather' => $hourly['weather'][0]['description'],
                'weather_icon' => $hourly['weather'][0]['icon'],
                'rain' => array_key_exists('rain', $hourly) ? $hourly['rain']['1h'] : 0,
            ];
        }
        return $readable;
    }
}
