<?php

namespace App\Tests\Class;

use App\Class\WeatherSettings;
use PHPUnit\Framework\TestCase;
use App\Service\SimpleCacheService;
use App\Class\OpenWeatherOneApiResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @covers App\Class\OpenWeatherOneApiResponse
 */
class OpenWeatherOneApiResponseTest extends TestCase
{
    public function testGetWeatherReadable(): void
    {
        $client = $this->createMock(originalClassName: HttpClientInterface::class);

        $clientCallback = function (string $method, string $uri, array $options) {
            $this->assertEquals(expected: 'GET', actual: $method);
            $this->assertEquals(
                expected: 'https://api.openweathermap.org/data/2.5/onecall' .
                    '?lat=&lon=&exclude=minutely&units=metric&lang=en&appid=',
                actual: $uri,
            );
            $this->assertEquals(expected: [], actual: $options);
            $res = $this->createMock(originalClassName: ResponseInterface::class);
            $resArr = [
                'hourly' => [],
                'daily' => [],
            ];
            $res->method('toArray')->willReturn($resArr);

            return $res;
        };

        $client->method('request')->willReturnCallBack($clientCallback);


        $weatherSettings = $this->createMock(originalClassName: WeatherSettings::class);
        $simpleCacheService = $this->createMock(originalClassName: SimpleCacheService::class);

        $testedClass = new OpenWeatherOneApiResponse(
            client: $client,
            weatherSettings: $weatherSettings,
            simpleCacheService: $simpleCacheService,
        );
        $readable = $testedClass->getWeatherReadable();
        $this->assertEquals(
            expected: [
                'hourly' => [],
                'daily' => [],
            ],
            actual: $readable,
        );
    }
}
