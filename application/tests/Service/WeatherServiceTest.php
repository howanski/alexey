<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Class\OpenWeatherOneApiResponse;
use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use App\Service\SimpleCacheService;
use App\Service\SimpleSettingsService;
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

        $service = new WeatherService(
            client: $client,
            simpleSettingsService: $simpleSettingsService,
            simpleCacheService: $simpleCacheService,
        );

        $weather = $service->getWeather();


        $this->assertEquals(
            expected: OpenWeatherOneApiResponse::class,
            actual: get_class($weather),
            message: 'Wrong weather format',
        );
    }
}
