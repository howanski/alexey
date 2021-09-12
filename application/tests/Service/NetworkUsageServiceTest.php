<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatistic;
use App\Service\NetworkUsageService;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NetworkStatisticRepository;
use App\Repository\NetworkStatisticTimeFrameRepository;

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

        $returnObj = new NetworkStatistic();
        $networkStatisticRepository->method('getLatestOne')->willReturn($returnObj);

        $service = new NetworkUsageService(
            em: $em,
            simpleSettingsService: $simpleSettingsService,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkStatisticRepository: $networkStatisticRepository,
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
        );

        $this->assertNotNull(
            actual: $service->getCurrentStatistic(),
            message: 'HiLink statistics not retrieved',
        );
    }

    public function testGetDataForChart(): void
    {
        $em = $this->createMock(originalClassName: EntityManagerInterface::class);
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $networkStatisticTimeFrameRepository =
            $this->createMock(originalClassName: NetworkStatisticTimeFrameRepository::class);
        $networkStatisticRepository = $this->createMock(originalClassName: NetworkStatisticRepository::class);

        $service = new NetworkUsageService(
            em: $em,
            simpleSettingsService: $simpleSettingsService,
            networkStatisticTimeFrameRepository: $networkStatisticTimeFrameRepository,
            networkStatisticRepository: $networkStatisticRepository,
        );

        $this->assertEquals(
            expected: [
                'labels' => [],
                'datasets' => [],
                'current' => [
                    'current_traffic_left' => 0,
                    'current_transfer_rate_left' => 0,
                    'current_transfer_rate' => 0,
                    'current_billing_frame_end' => 0,
                ],
                'throttling' => 'N. A.',
            ],
            actual: $service->getDataForChart('eee'),
        );
    }
}
