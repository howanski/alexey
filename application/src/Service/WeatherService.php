<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use App\Class\WeatherSettings;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WeatherService
{
    public function __construct(
        private HttpClientInterface $client,
        private SimpleSettingsService $simpleSettingsService,
        private SimpleCacheService $simpleCacheService,
        private TranslatorInterface $translator,
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

    public function getChartData(string $locale = 'en'): array
    {
        $chartData = [
            'hourly' => [
                'labels' => [],
                'datasets' => $this->getEmptyDatasetsForChart(),
            ],
            'daily' => [
                'labels' => [],
                'datasets' => $this->getEmptyDatasetsForChart(),
            ],
        ];
        $sourceData = $this->getWeather()->getWeatherReadable(locale: $locale);
        foreach ($sourceData['hourly'] as $hour) {
            $chartData['hourly']['labels'][] = $hour['time']->format('D H:i');
            $chartData['hourly']['datasets']['temperature']['data'][] = $hour['temperature'];
            $chartData['hourly']['datasets']['feels_like']['data'][] = $hour['temperature_feels_like'];
            $chartData['hourly']['datasets']['rain']['data'][] = $hour['rain'] + $hour['snow'];
            $chartData['hourly']['datasets']['wind_speed']['data'][] = $this->toKph($hour['wind_speed']);
        }
        $timeLayout = [
            [
                'key' => 'morn',
                'name' => 'Morning'
            ],
            [
                'key' => 'day',
                'name' => 'Day'
            ],
            [
                'key' => 'eve',
                'name' => 'Evening'
            ],
            [
                'key' => 'night',
                'name' => 'Night'
            ],
        ];
        foreach ($sourceData['daily'] as $day) {
            foreach ($timeLayout as $layout) {
                $chartData['daily']['labels'][] = $day['date'] . ' ' . $layout['name'];
                $chartData['daily']['datasets']['temperature']['data'][] = $day['temperature_detailed'][$layout['key']];
                $chartData['daily']['datasets']['feels_like']['data'][] =
                    $day['temperature_feels_like'][$layout['key']];
                $chartData['daily']['datasets']['rain']['data'][] = $day['rain'] + $day['snow'];
                $chartData['daily']['datasets']['wind_speed']['data'][] = $this->toKph($day['wind_speed']);
            }
        }
        return $chartData;
    }

    private function getEmptyDatasetsForChart(): array
    {
        $result = [];
        $chartConfs = [
            [
                'key' => 'temperature',
                'label' => $this->translator->trans('app.weather.temperature') . ' (°C)',
                'color' => [78, 115, 223],
            ],
            [
                'key' => 'feels_like',
                'label' => $this->translator->trans('app.weather.feels_like') . ' (°C)',
                'color' => [140, 115, 223],
            ],
            [
                'key' => 'wind_speed',
                'label' => $this->translator->trans('app.weather.wind_speed') . ' (km/h)',
                'color' => [140, 115, 100],
            ],
            [
                'key' => 'rain',
                'label' => $this->translator->trans('app.weather.rain_or_snow') . ' (mm)',
                'color' => [78, 222, 223],
            ],
        ];
        foreach ($chartConfs as $conf) {
            $result[$conf['key']] = [
                'label' => $conf['label'],
                'lineTension' => 0.3,
                'backgroundColor' => 'rgba(' . $conf['color'][0] . ', ' . $conf['color'][1] . ', '
                    . $conf['color'][2] . ', 0.05)',
                'borderColor' => 'rgba(' . $conf['color'][0] . ', ' . $conf['color'][1] . ', '
                    . $conf['color'][2] . ', 1)',
                'pointRadius' => 3,
                'pointBackgroundColor' => 'rgba(' . $conf['color'][0] . ', ' . $conf['color'][1] . ', ' .
                    $conf['color'][2] . ', 1)',
                'pointBorderColor' => 'rgba(' . $conf['color'][0] . ', ' . $conf['color'][1] . ', ' .
                    $conf['color'][2] . ', 1)',
                'pointHoverRadius' => 3,
                'pointHoverBackgroundColor' => 'rgba(' . $conf['color'][0] . ', ' . $conf['color'][1] . ', ' .
                    $conf['color'][2] . ', 1)',
                'pointHoverBorderColor' => 'rgba(' . $conf['color'][0] . ', ' . $conf['color'][1] . ', ' .
                    $conf['color'][2] . ', 1)',
                'pointHitRadius' => 10,
                'pointBorderWidth' => 2,
                'data' => [],
            ];
        }
        return $result;
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

    private function toKph(int|float $metersPerSecond): float
    {
        $kph = $metersPerSecond * 3.6;
        return round($kph);
    }
}
