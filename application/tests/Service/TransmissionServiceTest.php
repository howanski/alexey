<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatistic;
use App\Service\NetworkUsageService;
use App\Service\TransmissionService;
use App\Service\SimpleSettingsService;
use App\Entity\NetworkStatisticTimeFrame;

/**
 * @covers App\Service\TransmissionService
 */
final class TransmissionServiceTest extends TestCase
{
    public function testgetSimulationChartData(): void
    {
        $networkUsageService = $this->createMock(originalClassName: NetworkUsageService::class);

        $inSixHours = new \DateTime('now');
        $sixHours = new \DateInterval('PT6H');
        $inSixHours->add($sixHours);

        $timeFrame = new NetworkStatisticTimeFrame();
        $timeFrame->setBillingFrameEnd($inSixHours);
        $stat = new NetworkStatistic();
        $stat->setProbingTime(new \DateTime('now'));
        $stat->setTimeFrame($timeFrame);
        $networkUsageService->method('getLatestStatistic')->willReturn($stat);

        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $settings = [
            'TRANSMISSION_THROTTLE_ACTIVE' => 'BOOL_YES',
            'TRANSMISSION_HOST' => 'BOOL_YES',
            'TRANSMISSION_USER' => 'USR',
            'TRANSMISSION_PASSWORD' => 'PASS',
            'TRANSMISSION_TARGET_SPEED' => '50',
            'TRANSMISSION_TARGET_SPEED_BUMPING' => 'yy',
            'TRANSMISSION_AGGRESSION' => 'tt',
            'TRANSMISSION_AGGRESSION_ADAPT' => 'trt',
        ];
        $simpleSettingsService->method('getSettings')->willReturn($settings);

        $service = new TransmissionService(
            networkUsageService: $networkUsageService,
            simpleSettingsService: $simpleSettingsService,
        );

        $result = $service->getSimulationChartData();
        unset($result['speed']['labels']);
        unset($result['speed']['datasets'][0]['data']);
        unset($result['time']['labels']);
        unset($result['time']['datasets'][0]['data']);

        $this->assertEquals(
            expected: [
                'speed' => [
                    'datasets' => [
                        [
                            'label' => 'Throttling (kB/s)',
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
                        ],
                    ],
                ],
                'time' => [
                    'datasets' => [
                        [
                            'label' => 'Throttling (kB/s)',
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
                        ],
                    ],
                ],
            ],
            actual: $result,
            message: 'Simulation data malformed',
        );
    }
}