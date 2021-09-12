<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\NetworkStatistic;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Entity\NetworkStatistic
 */
final class NetworkStatisticTest extends TestCase
{
    public function testProbingTime(): void
    {
        $stat = new NetworkStatistic();
        $this->assertEquals(
            expected: DateTime::class,
            actual: get_class($stat->getProbingTime()),
            message: 'Probing time not set on empty object'
        );

        $probingTime = new DateTime('now');
        $stat->setProbingTime($probingTime);

        $this->assertEquals(
            expected: $probingTime,
            actual: $stat->getProbingTime(),
            message: 'Probing time changed after set'
        );
    }
}
