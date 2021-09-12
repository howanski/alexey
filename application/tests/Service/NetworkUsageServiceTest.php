<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\NetworkStatistic;
use PHPUnit\Framework\TestCase;
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
}
