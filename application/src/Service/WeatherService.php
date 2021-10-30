<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use App\Class\WeatherSettings;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private $dayTranslations = [];

    public function __construct(
        private HttpClientInterface $client,
        private SimpleSettingsService $simpleSettingsService,
        private SimpleCacheService $simpleCacheService,
        private AlexeyTranslator $translator,
    ) {
        $this->dayTranslations = [
            'Mon' => $translator->trans('app.time.day_short.monday'),
            'Tue' => $translator->trans('app.time.day_short.tuesday'),
            'Wed' => $translator->trans('app.time.day_short.wednesday'),
            'Thu' => $translator->trans('app.time.day_short.thursday'),
            'Fri' => $translator->trans('app.time.day_short.friday'),
            'Sat' => $translator->trans('app.time.day_short.saturday'),
            'Sun' => $translator->trans('app.time.day_short.sunday'),
        ];
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

    public function getChartData($locale): array
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
            $chartData['hourly']['labels'][] = $this->dayTrans($hour['time']->format('D H:i'));
            $chartData['hourly']['datasets']['temperature']['data'][] = round($hour['temperature']);
            $chartData['hourly']['datasets']['feels_like']['data'][] = round($hour['temperature_feels_like']);
            $chartData['hourly']['datasets']['rain']['data'][] = round($hour['rain'] + $hour['snow']);
            $chartData['hourly']['datasets']['wind_speed']['data'][] =
                round($this->metersPerSecondToKpH($hour['wind_speed']));
        }
        $timeLayout = [
            [
                'key' => 'morn',
                'name' => $this->translator->trans('app.time.of_day.morning'),
            ],
            [
                'key' => 'day',
                'name' => $this->translator->trans('app.time.of_day.day')
            ],
            [
                'key' => 'eve',
                'name' => $this->translator->trans('app.time.of_day.evening')
            ],
            [
                'key' => 'night',
                'name' => $this->translator->trans('app.time.of_day.night')
            ],
        ];
        foreach ($sourceData['daily'] as $day) {
            foreach ($timeLayout as $layout) {
                $chartData['daily']['labels'][] = $this->dayTrans($day['date']) . ' ' . $layout['name'];
                $chartData['daily']['datasets']['temperature']['data'][] =
                    round($day['temperature_detailed'][$layout['key']]);
                $chartData['daily']['datasets']['feels_like']['data'][] =
                    round($day['temperature_feels_like'][$layout['key']]);
                $chartData['daily']['datasets']['rain']['data'][] = round($day['rain'] + $day['snow']);
                $chartData['daily']['datasets']['wind_speed']['data'][] =
                    round($this->metersPerSecondToKpH($day['wind_speed']));
            }
        }
        return $chartData;
    }

    private function dayTrans(string $poorlyFormattedDate): string
    {
        foreach ($this->dayTranslations as $key => $val) {
            $poorlyFormattedDate = str_replace(
                search: $key,
                replace: $val,
                subject: $poorlyFormattedDate,
            );
        }
        return $poorlyFormattedDate;
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
