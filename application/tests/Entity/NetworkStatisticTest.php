<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatistic;
use App\Entity\NetworkStatisticTimeFrame;

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
            message: 'Probing time not set on empty object',
        );

        $probingTime = new DateTime('now');
        $stat->setProbingTime($probingTime);

        $this->assertEquals(
            expected: $probingTime,
            actual: $stat->getProbingTime(),
            message: 'Probing time changed after set',
        );
    }

    public function testDataUploadedInFrame(): void
    {
        $stat = new NetworkStatistic();
        $integer = 123;
        $stat->setDataUploadedInFrame($integer);

        $this->assertEquals(
            expected: $integer,
            actual: $stat->getDataUploadedInFrame(),
            message: 'DataUploadedInFrame changed after set',
        );
    }

    public function testDataDownloadedInFrame(): void
    {
        $stat = new NetworkStatistic();
        $integer = 123;
        $stat->setDataDownloadedInFrame($integer);

        $this->assertEquals(
            expected: $integer,
            actual: $stat->getDataDownloadedInFrame(),
            message: 'DataDownloadedInFrame changed after set',
        );
    }

    public function testTimeFrame(): void
    {
        $stat = new NetworkStatistic();
        $timeFrame = new NetworkStatisticTimeFrame();
        $stat->setTimeFrame($timeFrame);

        $this->assertEquals(
            expected: $timeFrame,
            actual: $stat->getTimeFrame(),
            message: 'TimeFrame changed after set',
        );
    }

    public function testReferencePoint(): void
    {
        $stat = new NetworkStatistic();
        $referencePoint = new NetworkStatistic();
        $stat->setReferencePoint($referencePoint);
        $this->assertTrue(true);
    }

    public function testGetTimeLeftTillFrameEnd(): void
    {
        $stat = new NetworkStatistic();
        $timeFrame = new NetworkStatisticTimeFrame();
        $billingFrameEnd = new \DateTime('tomorrow');
        $timeFrame->setBillingFrameEnd($billingFrameEnd);
        $probingTime = new \DateTime('today');
        $stat->setProbingTime($probingTime);
        $stat->setTimeFrame($timeFrame);
        $this->assertEquals(
            expected: 86400, //day
            actual: $stat->getTimeLeftTillFrameEnd(),
        );
    }

    public function testGetTimePassedFromReferencePoint()
    {
        $probingTime = new \DateTime('now');
        $olderProbingTime = new \DateTime('now');
        $interval = new \DateInterval('PT13S');
        $olderProbingTime->sub($interval);

        $stat = new NetworkStatistic();
        $stat->setProbingTime($probingTime);

        $olderStat = new NetworkStatistic();
        $olderStat->setProbingTime($olderProbingTime);
        $stat->setReferencePoint($olderStat);

        $this->assertEquals(
            expected: 13,
            actual: $stat->getTimePassedFromReferencePoint(),
            message: 'Wrongly measured time passed',
        );
    }
}
