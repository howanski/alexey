<?php

declare(strict_types=1);

namespace App\Tests\Class;

use App\Model\TransmissionSettings;
use App\Service\SimpleSettingsService;
use PHPUnit\Framework\TestCase;

final class TransmissionSettingsTest extends TestCase
{
    public function testSelfConfigure(): void
    {
        $settings = new TransmissionSettings();
        $simpleSettingsService = $this->createMock(originalClassName: SimpleSettingsService::class);
        $settingsArray = [
            'TRANSMISSION_THROTTLE_ACTIVE' => 'SET_1',
            'TRANSMISSION_HOST' => 'SET_2',
            'TRANSMISSION_USER' => 'SET_3',
            'TRANSMISSION_PASSWORD' => 'SET_4',
            'TRANSMISSION_TARGET_SPEED' => 'SET_5',
            'TRANSMISSION_TARGET_SPEED_BUMPING' => 'SET_6',
            'TRANSMISSION_AGGRESSION' => '7',
            'TRANSMISSION_AGGRESSION_ADAPT' => 'SET_8',
            'TRANSMISSION_TARGET_SPEED_FRAME' => 'SET_9',
            'TRANSMISSION_TARGET_SPEED_MAX' => 123456,
        ];
        $simpleSettingsService->method('getSettings')->willReturn($settingsArray);

        $settings->selfConfigure($simpleSettingsService);

        $this->assertEquals(
            expected: 'SET_1',
            actual: $settings->getIsActive(),
        );

        $this->assertEquals(
            expected: 'SET_2',
            actual: $settings->getHost(),
        );

        $this->assertEquals(
            expected: 'SET_3',
            actual: $settings->getUser(),
        );

        $this->assertEquals(
            expected: 'SET_4',
            actual: $settings->getPassword(),
        );

        $this->assertEquals(
            expected: 'SET_5',
            actual: $settings->getTargetSpeed(),
        );

        $this->assertEquals(
            expected: 'SET_6',
            actual: $settings->getAllowSpeedBump(),
        );

        $this->assertEquals(
            expected: '7',
            actual: $settings->getAlgorithmAggression(),
        );

        $this->assertEquals(
            expected: 'SET_8',
            actual: $settings->getAggressionAdapt(),
        );

        $this->assertEquals(
            expected: 'SET_9',
            actual: $settings->getTargetFrame(),
        );

        $this->assertEquals(
            expected: 123456,
            actual: $settings->getTargetSpeedMax(),
        );
    }
}
