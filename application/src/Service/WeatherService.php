<?php

declare(strict_types=1);

namespace App\Service;

use Carbon\Carbon;
use App\Class\WeatherSettings;
use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    public function __construct(
        private HttpClientInterface $client,
        private SimpleSettingsService $simpleSettingsService,
        private SimpleCacheService $simpleCacheService,
        private AlexeyTranslator $translator,
    ) {
    }

    public function getWeather(): OpenWeatherOneApiResponse
    {
        return $this->prepareWeatherObject();
    }

    public function showWeatherOnDashboard(): bool
    {
        $settings = $this->getWeatherSettings();
        return (SimpleSettingsService::UNIVERSAL_TRUTH === $settings->getShowOnDashboard());
    }

    public function getChartData($locale, $type): array
    {
        if ('daily' === $type) {
            return $this->getDailyChartData(locale: $locale);
        } else {
            return $this->getHourlyChartData(locale: $locale);
        }
    }

    private function getDailyChartData(string $locale)
    {
        $chartData = [
            'labels' => [],
            'datasets' => $this->getEmptyDatasetsForChart(),
        ];
        $sourceData = $this->getWeather()->getWeatherReadable(locale: $locale);

        $dayPartsCodes = ['morn', 'day', 'eve', 'night'];

        foreach ($sourceData['daily'] as $day) {
            foreach ($dayPartsCodes as $dayPart) {
                $label = $this->translator->translateTime(
                    timeUnit: 'day',
                    type: 'short',
                    value: $day['date'],
                );
                $label .= ' ';
                $label .= $this->translator->translateTime(
                    timeUnit: 'day_part',
                    value: $dayPart,
                );
                $chartData['labels'][] = $label;
                $chartData['datasets']['temperature']['data'][] =
                    round($day['temperature_detailed'][$dayPart]);
                $chartData['datasets']['feels_like']['data'][] =
                    round($day['temperature_feels_like'][$dayPart]);
                $chartData['datasets']['rain']['data'][] = round($day['rain'] + $day['snow']);
                $chartData['datasets']['wind_speed']['data'][] =
                    round($this->metersPerSecondToKpH($day['wind_speed']));
            }
        }
        return $chartData;
    }

    private function getHourlyChartData(string $locale)
    {
        $chartData = [
            'labels' => [],
            'datasets' => $this->getEmptyDatasetsForChart(),
        ];
        $sourceData = $this->getWeather()->getWeatherReadable(locale: $locale);
        foreach ($sourceData['hourly'] as $hour) {
            /**
             * @var Carbon $time
             */
            $time = $hour['time'];
            $hourString = $time->format('H:i');
            $day = $time->format('l');
            $label = $this->translator->translateTime(
                timeUnit: 'day',
                type: 'short',
                value: $day,
            );
            $label .= ' ' . $hourString;
            $chartData['labels'][] = $label;
            $chartData['datasets']['temperature']['data'][] = round($hour['temperature']);
            $chartData['datasets']['feels_like']['data'][] = round($hour['temperature_feels_like']);
            $chartData['datasets']['rain']['data'][] = round($hour['rain'] + $hour['snow']);
            $chartData['datasets']['wind_speed']['data'][] =
                round($this->metersPerSecondToKpH($hour['wind_speed']));
        }
        return $chartData;
    }

    private function getEmptyDatasetsForChart(): array
    {
        $result = [];
        $chartConfs = [
            [
                'key' => 'temperature',
                'label' => $this->translator->translateString(
                    translationId: 'temperature',
                    module: 'weather',
                ) . ' (°C)',
                'color' => [78, 115, 223],
            ],
            [
                'key' => 'feels_like',
                'label' => $this->translator->translateString(
                    translationId: 'feels_like',
                    module: 'weather',
                ) . ' (°C)',
                'color' => [140, 115, 223],
            ],
            [
                'key' => 'wind_speed',
                'label' => $this->translator->translateString(
                    translationId: 'wind_speed',
                    module: 'weather',
                ) . ' (km/h)',
                'color' => [140, 115, 100],
            ],
            [
                'key' => 'rain',
                'label' => $this->translator->translateString(
                    translationId: 'rain_or_snow',
                    module: 'weather',
                ) . ' (mm)',
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

    private function metersPerSecondToKpH(int|float $metersPerSecond): float
    {
        $kph = $metersPerSecond * 3.6;
        return round($kph);
    }
}
