<?php

namespace App\Class;

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
        $this->ensureWeatherDataFetched(); // TODO: move me to getters - no unneeded API calls
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
}
