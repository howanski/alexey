<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatistic;
use App\Service\AlexeyTranslator;
use App\Service\NetworkUsageService;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\NetworkStatisticTimeFrame;
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;
use App\Service\MikrotikService;
use App\Service\NetworkUsageProviderSettings;
use App\Service\SimpleCacheService;

final class NetworkUsageServiceTest extends TestCase
{
    public function testGetLatestStatistics(): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);
        $translator = $this->createMock(originalClassName: AlexeyTranslator::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('translateString')->willReturnCallback($translatorFunc);

        $returnObj = new NetworkStatistic();
        $networkStatisticRepository->method('getLatestOne')->willReturn($returnObj);

        $networkUsageProviderSettings = $this->createMock(originalClassName: NetworkUsageProviderSettings::class);
        $mikrotikService = $this->createMock(originalClassName: MikrotikService::class);

        $service = new NetworkUsageService(
            em: $em,
            mikrotikService: $mikrotikService,
            networkStatisticRepository: $networkStatisticRepository,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkUsageProviderSettings: $networkUsageProviderSettings,
            simpleCacheService: $this->createMock(originalClassName: SimpleCacheService::class),
            simpleSettingsService: $simpleSettingsService,
            translator: $translator,
        );

        $this->assertEquals(
            expected: $returnObj,
            actual: $service->getLatestStatistic(),
            message: 'Wrong entity provided',
        );
    }

    public function testGetCurrentStatistic(): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);
        $translator = $this->createMock(originalClassName: AlexeyTranslator::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('translateString')->willReturnCallback($translatorFunc);

        $settingsArray = [
            'NETWORK_USAGE_PROVIDER_TYPE' => 'HILINK',
            'NETWORK_USAGE_PROVIDER_ADDRESS' => '0.0.0.0',
            'NETWORK_USAGE_PROVIDER_PASSWORD' => 'XXXX',
            'NETWORK_USAGE_SHOW_ON_DASHBOARD' => 'XXXXX',
        ];

        $simpleSettingsService->method('getSettings')->willReturn($settingsArray);

        $networkUsageProviderSettings = $this->createMock(originalClassName: NetworkUsageProviderSettings::class);
        $mikrotikService = $this->createMock(originalClassName: MikrotikService::class);

        $service = new NetworkUsageService(
            em: $em,
            mikrotikService: $mikrotikService,
            networkStatisticRepository: $networkStatisticRepository,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkUsageProviderSettings: $networkUsageProviderSettings,
            simpleCacheService: $this->createMock(originalClassName: SimpleCacheService::class),
            simpleSettingsService: $simpleSettingsService,
            translator: $translator,
        );

        $service->getCurrentStatistic();

        $this->assertTrue(
            condition: true,
            message: 'No way to test real-life external service.',
        );
    }

    /**
     * @dataProvider chartTypeProvider
     */
    public function testGetDataForChart(string $chartDataType, array $expectedValue): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);
        $translator = $this->createMock(originalClassName: AlexeyTranslator::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('translateString')->willReturnCallback($translatorFunc);

        $sixHours = new \DateInterval('PT6H');

        $sixHoursAgo = new \DateTime('now');
        $sixHoursAgo->sub($sixHours);

        $inSixHours = new \DateTime('now');
        $inSixHours->add($sixHours);

        $timeFrame = new NetworkStatisticTimeFrame();
        $timeFrame->setBillingFrameStart($sixHoursAgo);
        $timeFrame->setBillingFrameEnd($inSixHours);

        $networkStatisticOld = new NetworkStatistic();
        $networkStatisticOld->setProbingTime($sixHoursAgo);

        $networkStatistic = new NetworkStatistic();
        $networkStatistic->setTimeFrame($timeFrame);
        $networkStatistic->setReferencePoint($networkStatisticOld);

        $networkStatisticRepository->method('getLatestOne')->willReturn($networkStatistic);

        $networkUsageProviderSettings = $this->createMock(originalClassName: NetworkUsageProviderSettings::class);
        $mikrotikService = $this->createMock(originalClassName: MikrotikService::class);


        $service = new NetworkUsageService(
            em: $em,
            mikrotikService: $mikrotikService,
            networkStatisticRepository: $networkStatisticRepository,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkUsageProviderSettings: $networkUsageProviderSettings,
            simpleCacheService: $this->createMock(originalClassName: SimpleCacheService::class),
            simpleSettingsService: $simpleSettingsService,
            translator: $translator,
        );

        $this->assertEquals(
            expected: $expectedValue,
            actual: $service->getDataForChart(chartDataType: $chartDataType, locale: 'en'),
        );
    }

    public function chartTypeProvider(): array
    {
        return [
            ['randomString', [
                'labels' => [],
                'datasets' => [],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
            ['today', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_current_speed (kB/s)',
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
                    'speed_left' => [
                        'label' => 'translated_optimal_speed (kB/s)',
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
                    'speed_left_midnight' => [
                        'label' => 'translated_optimal_speed_midnight (kB/s)',
                        'lineTension' => 0.3,
                        'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
                        'borderColor' => 'rgba(42, 79, 11, 1)',
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverRadius' => 3,
                        'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHitRadius' => 10,
                        'pointBorderWidth' => 2,
                        'data' => [],
                    ],
                ],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
            ['last_week', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_current_speed (kB/s)',
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
                    'speed_left' => [
                        'label' => 'translated_optimal_speed (kB/s)',
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
                    'speed_left_midnight' => [
                        'label' => 'translated_optimal_speed_midnight (kB/s)',
                        'lineTension' => 0.3,
                        'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
                        'borderColor' => 'rgba(42, 79, 11, 1)',
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverRadius' => 3,
                        'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHitRadius' => 10,
                        'pointBorderWidth' => 2,
                        'data' => [],
                    ],
                ],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
            ['current_billing_frame', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_current_speed (kB/s)',
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
                    'speed_left' => [
                        'label' => 'translated_optimal_speed (kB/s)',
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
                    'speed_left_midnight' => [
                        'label' => 'translated_optimal_speed_midnight (kB/s)',
                        'lineTension' => 0.3,
                        'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
                        'borderColor' => 'rgba(42, 79, 11, 1)',
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverRadius' => 3,
                        'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHitRadius' => 10,
                        'pointBorderWidth' => 2,
                        'data' => [],
                    ],
                ],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
            ['last_48_hours', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_current_speed (kB/s)',
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
                    'speed_left' => [
                        'label' => 'translated_optimal_speed (kB/s)',
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
                    'speed_left_midnight' => [
                        'label' => 'translated_optimal_speed_midnight (kB/s)',
                        'lineTension' => 0.3,
                        'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
                        'borderColor' => 'rgba(42, 79, 11, 1)',
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverRadius' => 3,
                        'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHitRadius' => 10,
                        'pointBorderWidth' => 2,
                        'data' => [],
                    ],
                ],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
            ['last_2_hours', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_current_speed (kB/s)',
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
                    'speed_left' => [
                        'label' => 'translated_optimal_speed (kB/s)',
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
                    'speed_left_midnight' => [
                        'label' => 'translated_optimal_speed_midnight (kB/s)',
                        'lineTension' => 0.3,
                        'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
                        'borderColor' => 'rgba(42, 79, 11, 1)',
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverRadius' => 3,
                        'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHitRadius' => 10,
                        'pointBorderWidth' => 2,
                        'data' => [],
                    ],
                ],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
            ['last_10_minutes', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_current_speed (kB/s)',
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
                    'speed_left' => [
                        'label' => 'translated_optimal_speed (kB/s)',
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
                    'speed_left_midnight' => [
                        'label' => 'translated_optimal_speed_midnight (kB/s)',
                        'lineTension' => 0.3,
                        'backgroundColor' => 'rgba(42, 79, 11, 0.05)',
                        'borderColor' => 'rgba(42, 79, 11, 1)',
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverRadius' => 3,
                        'pointHoverBackgroundColor' => 'rgba(42, 79, 11, 1)',
                        'pointHoverBorderColor' => 'rgba(42, 79, 11, 1)',
                        'pointHitRadius' => 10,
                        'pointBorderWidth' => 2,
                        'data' => [],
                    ],
                ],
                'bonusPayload' => [
                    'current_traffic_left' => '0 B | 0 B',
                    'current_transfer_rate_left' => '0 B/s | 0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                    'current_throttling' => 'N. A.',
                ],
            ]],
        ];
    }
}
