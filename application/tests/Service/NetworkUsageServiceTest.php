<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatistic;
use App\Service\NetworkUsageService;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\NetworkStatisticTimeFrame;
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers App\Service\NetworkUsageService
 */
final class NetworkUsageServiceTest extends TestCase
{
    public function testGetLatestStatistics(): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);
        $translator = $this->createMock(originalClassName: TranslatorInterface::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('trans')->willReturnCallback($translatorFunc);

        $returnObj = new NetworkStatistic();
        $networkStatisticRepository->method('getLatestOne')->willReturn($returnObj);

        $service = new NetworkUsageService(
            em: $em,
            simpleSettingsService: $simpleSettingsService,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkStatisticRepository: $networkStatisticRepository,
            translator: $translator,
        );

        $this->assertEquals(
            expected: $returnObj,
            actual: $service->getLatestStatistic(),
            message: 'Wrong entity provided',
        );
    }

    public function testGetConnectionSettings(): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);
        $translator = $this->createMock(originalClassName: TranslatorInterface::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('trans')->willReturnCallback($translatorFunc);

        $returnObj = [
            'NETWORK_USAGE_PROVIDER_TYPE' => 'XX',
            'NETWORK_USAGE_PROVIDER_ADDRESS' => 'XXX',
            'NETWORK_USAGE_PROVIDER_PASSWORD' => 'XXXX',
            'NETWORK_USAGE_SHOW_ON_DASHBOARD' => 'XXXXX',
        ];

        $simpleSettingsService->method('getSettings')->willReturn($returnObj);

        $service = new NetworkUsageService(
            em: $em,
            simpleSettingsService: $simpleSettingsService,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkStatisticRepository: $networkStatisticRepository,
            translator: $translator,
        );

        $actualSettings = $service->getConnectionSettings();

        $this->assertEquals(
            expected: 'XX',
            actual: $actualSettings->getProviderType(),
        );

        $this->assertEquals(
            expected: 'XXX',
            actual: $actualSettings->getAddress(),
        );

        $this->assertEquals(
            expected: 'XXXX',
            actual: $actualSettings->getPassword(),
        );

        $this->assertEquals(
            expected: 'XXXXX',
            actual: $actualSettings->getShowOnDashboard(),
        );
    }

    public function testGetCurrentStatistic(): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);
        $translator = $this->createMock(originalClassName: TranslatorInterface::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('trans')->willReturnCallback($translatorFunc);

        $settingsArray = [
            'NETWORK_USAGE_PROVIDER_TYPE' => 'HILINK',
            'NETWORK_USAGE_PROVIDER_ADDRESS' => '0.0.0.0',
            'NETWORK_USAGE_PROVIDER_PASSWORD' => 'XXXX',
            'NETWORK_USAGE_SHOW_ON_DASHBOARD' => 'XXXXX',
        ];

        $simpleSettingsService->method('getSettings')->willReturn($settingsArray);

        $service = new NetworkUsageService(
            em: $em,
            simpleSettingsService: $simpleSettingsService,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkStatisticRepository: $networkStatisticRepository,
            translator: $translator,
        );

        $this->assertNotNull(
            actual: $service->getCurrentStatistic(),
            message: 'HiLink statistics not retrieved',
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
        $translator = $this->createMock(originalClassName: TranslatorInterface::class);
        $translatorFunc = function (string $string) {
            return 'translated_' . $string;
        };
        $translator->method('trans')->willReturnCallback($translatorFunc);

        $sixHours = new \DateInterval('PT6H');

        $sixHoursAgo = new \DateTime('now');
        $sixHoursAgo->sub($sixHours);

        $inSixHours = new \DateTime('now');
        $inSixHours->Add($sixHours);

        $timeFrame = new NetworkStatisticTimeFrame();
        $timeFrame->setBillingFrameStart($sixHoursAgo);
        $timeFrame->setBillingFrameEnd($inSixHours);

        $networkStatisticOld = new NetworkStatistic();
        $networkStatisticOld->setProbingTime($sixHoursAgo);

        $networkStatistic = new NetworkStatistic();
        $networkStatistic->setTimeFrame($timeFrame);
        $networkStatistic->setReferencePoint($networkStatisticOld);

        $networkStatisticRepository->method('getLatestOne')->willReturn($networkStatistic);
        $service = new NetworkUsageService(
            em: $em,
            simpleSettingsService: $simpleSettingsService,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkStatisticRepository: $networkStatisticRepository,
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
            ['today', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_app.network.network_usage.current_speed (kB/s)',
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
                        'label' => 'translated_app.network.network_usage.optimal_speed (kB/s)',
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
            ['week', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_app.network.network_usage.current_speed (kB/s)',
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
                        'label' => 'translated_app.network.network_usage.optimal_speed (kB/s)',
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
            ['month', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_app.network.network_usage.current_speed (kB/s)',
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
                        'label' => 'translated_app.network.network_usage.optimal_speed (kB/s)',
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
            ['currentFrame', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_app.network.network_usage.current_speed (kB/s)',
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
                        'label' => 'translated_app.network.network_usage.optimal_speed (kB/s)',
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
            ['twoHours', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_app.network.network_usage.current_speed (kB/s)',
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
                        'label' => 'translated_app.network.network_usage.optimal_speed (kB/s)',
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
            ['tenMinutes', [
                'labels' => [],
                'datasets' => [
                    'speed_relative' => [
                        'label' => 'translated_app.network.network_usage.current_speed (kB/s)',
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
                        'label' => 'translated_app.network.network_usage.optimal_speed (kB/s)',
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
                'current' => [
                    'current_traffic_left' => '0 B',
                    'current_transfer_rate_left' => '0 B/s',
                    'current_transfer_rate' => '0 B/s',
                    'current_billing_frame_end' => '5 hours from now',
                ],
                'throttling' => 'N. A.',
            ]],
        ];
    }
}
