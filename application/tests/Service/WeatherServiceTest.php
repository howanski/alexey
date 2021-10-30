<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use App\Service\AlexeyTranslator;
use App\Service\SimpleCacheService;
use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @covers App\Service\WeatherService
 */
final class WeatherServiceTest extends TestCase
{
    public function testGetWeather(): void
    {
        $client = $this->createMock(originalClassName: HttpClientInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $settingsArray = [
            'WEATHER_LAT' => 'a',
            'WEATHER_LON' => 'b',
            'WEATHER_API_KEY' => 'c',
            'WEATHER_SHOW_ON_DASHBOARD' => 'd',
        ];
        $simpleSettingsService->method('getSettings')->willReturn($settingsArray);
        $simpleCacheService = $this->createMock(originalClassName: SimpleCacheService::class);
        $translator = $this->createMock(originalClassName: AlexeyTranslator::class);

        $service = new WeatherService(
            client: $client,
            simpleSettingsService: $simpleSettingsService,
            simpleCacheService: $simpleCacheService,
            translator: $translator,
        );

        $weather = $service->getWeather();


        $this->assertEquals(
            expected: OpenWeatherOneApiResponse::class,
            actual: get_class($weather),
            message: 'Wrong weather format',
        );
    }

    public function testGetChartData(): void
    {
        $client = $this->createMock(originalClassName: HttpClientInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $simpleCacheService = $this->createMock(originalClassName: SimpleCacheService::class);

        $settingsArray = [
            'WEATHER_LAT' => 'a',
            'WEATHER_LON' => 'b',
            'WEATHER_API_KEY' => 'c',
            'WEATHER_SHOW_ON_DASHBOARD' => 'd',
        ];
        $simpleSettingsService->method('getSettings')->willReturn($settingsArray);


        $weatherMock = [
            'hourly' => [],
            'daily' => [],
        ];
        $weatherResponse = $this->createMock(originalClassName: ResponseInterface::class);
        $weatherResponse->method('toArray')->willReturn($weatherMock);
        $client->method('request')->willReturn($weatherResponse);
        $translator = $this->createMock(originalClassName: AlexeyTranslator::class);
        $transCallback = function (string $string) {
            return 'trans_' . $string;
        };
        $translator->method('translateString')->willReturnCallback($transCallback);

        $service = new WeatherService(
            client: $client,
            simpleSettingsService: $simpleSettingsService,
            simpleCacheService: $simpleCacheService,
            translator: $translator,
        );

        $this->assertEquals(
            expected: [
                'hourly' => [
                    'labels' => [],
                    'datasets' => [
                        'temperature' => [
                            'label' => 'trans_temperature (°C)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
                            'borderColor' => 'rgba(78, 115, 223, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(78, 115, 223, 1)',
                            'pointBorderColor' => 'rgba(78, 115, 223, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(78, 115, 223, 1)',
                            'pointHoverBorderColor' => 'rgba(78, 115, 223, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                        'feels_like' => [
                            'label' => 'trans_feels_like (°C)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(140, 115, 223, 0.05)',
                            'borderColor' => 'rgba(140, 115, 223, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(140, 115, 223, 1)',
                            'pointBorderColor' => 'rgba(140, 115, 223, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(140, 115, 223, 1)',
                            'pointHoverBorderColor' => 'rgba(140, 115, 223, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                        'wind_speed' => [
                            'label' => 'trans_wind_speed (km/h)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(140, 115, 100, 0.05)',
                            'borderColor' => 'rgba(140, 115, 100, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(140, 115, 100, 1)',
                            'pointBorderColor' => 'rgba(140, 115, 100, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(140, 115, 100, 1)',
                            'pointHoverBorderColor' => 'rgba(140, 115, 100, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                        'rain' => [
                            'label' => 'trans_rain_or_snow (mm)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(78, 222, 223, 0.05)',
                            'borderColor' => 'rgba(78, 222, 223, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(78, 222, 223, 1)',
                            'pointBorderColor' => 'rgba(78, 222, 223, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(78, 222, 223, 1)',
                            'pointHoverBorderColor' => 'rgba(78, 222, 223, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                    ],
                ],
                'daily' => [
                    'labels' => [],
                    'datasets' => [
                        'temperature' => [
                            'label' => 'trans_temperature (°C)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
                            'borderColor' => 'rgba(78, 115, 223, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(78, 115, 223, 1)',
                            'pointBorderColor' => 'rgba(78, 115, 223, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(78, 115, 223, 1)',
                            'pointHoverBorderColor' => 'rgba(78, 115, 223, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                        'feels_like' => [
                            'label' => 'trans_feels_like (°C)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(140, 115, 223, 0.05)',
                            'borderColor' => 'rgba(140, 115, 223, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(140, 115, 223, 1)',
                            'pointBorderColor' => 'rgba(140, 115, 223, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(140, 115, 223, 1)',
                            'pointHoverBorderColor' => 'rgba(140, 115, 223, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                        'wind_speed' => [
                            'label' => 'trans_wind_speed (km/h)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(140, 115, 100, 0.05)',
                            'borderColor' => 'rgba(140, 115, 100, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(140, 115, 100, 1)',
                            'pointBorderColor' => 'rgba(140, 115, 100, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(140, 115, 100, 1)',
                            'pointHoverBorderColor' => 'rgba(140, 115, 100, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                        'rain' => [
                            'label' => 'trans_rain_or_snow (mm)',
                            'lineTension' => 0.3,
                            'backgroundColor' => 'rgba(78, 222, 223, 0.05)',
                            'borderColor' => 'rgba(78, 222, 223, 1)',
                            'pointRadius' => 3,
                            'pointBackgroundColor' => 'rgba(78, 222, 223, 1)',
                            'pointBorderColor' => 'rgba(78, 222, 223, 1)',
                            'pointHoverRadius' => 3,
                            'pointHoverBackgroundColor' => 'rgba(78, 222, 223, 1)',
                            'pointHoverBorderColor' => 'rgba(78, 222, 223, 1)',
                            'pointHitRadius' => 10,
                            'pointBorderWidth' => 2,
                            'data' => [],
                        ],
                    ],
                ],
            ],
            actual: $service->getChartData('en'),
            message: '---!---> Weather chart data wrongly formed.',
        );
    }
}
