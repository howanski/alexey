<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatistic;
use App\Entity\NetworkStatisticTimeFrame;

final class NetworkStatisticTest extends TestCase
{
    public function testProbingTime(): void
    {
        $stat = new NetworkStatistic();
        $this->assertEquals(
            expected: DateTime::class,
            actual: get_class($stat->getProbingTime()),
            message: '---!---> Probing time not set on empty object',
        );

        $probingTime = new DateTime('now');
        $stat->setProbingTime($probingTime);

        $this->assertEquals(
            expected: $probingTime,
            actual: $stat->getProbingTime(),
            message: '---!---> Probing time changed after set',
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
            message: '---!---> DataUploadedInFrame changed after set',
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
            message: '---!---> DataDownloadedInFrame changed after set',
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
            message: '---!---> TimeFrame changed after set',
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

    public function testGetTimePassedFromReferencePoint(): void
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
            message: '---!---> Wrongly measured time passed',
        );
    }

    public function testDataConsumptionFromReferencePoint(): void
    {
        $probingTime = new \DateTime('now');
        $olderProbingTime = new \DateTime('now');
        $interval = new \DateInterval('PT13S');
        $olderProbingTime->sub($interval);

        $oneDay = new \DateInterval('P1D');
        $tomorrow = new \DateTime('now');
        $tomorrow->add($oneDay);

        $timeFrame = new NetworkStatisticTimeFrame();
        $timeFrame->setBillingFrameDataLimit(15001);
        $timeFrame->setBillingFrameEnd($tomorrow);

        $stat = new NetworkStatistic();
        $stat->setProbingTime($probingTime);
        $stat->setDataDownloadedInFrame(200);
        $stat->setDataUploadedInFrame(300);
        $stat->setTimeFrame($timeFrame);

        $olderStat = new NetworkStatistic();
        $olderStat->setProbingTime($olderProbingTime);
        $olderStat->setDataDownloadedInFrame(100);
        $olderStat->setDataUploadedInFrame(100);

        $stat->setReferencePoint($olderStat);

        $this->assertEquals(
            expected: 100,
            actual: $stat->getDataDownloadedFromReferencePoint(),
            message: '---!---> Wrongly measured data consumption',
        );

        $this->assertEquals(
            expected: 200,
            actual: $stat->getDataUploadedFromReferencePoint(),
            message: '---!---> Wrongly measured data consumption',
        );

        $this->assertEquals(
            expected: 300,
            actual: $stat->getTotalTrafficFromReferencePoint(),
            message: '---!---> Wrongly measured data consumption',
        );

        $this->assertEquals(
            expected: 7.6923,
            actual: round(num: $stat->getDownloadSpeedFromReferencePoint(), precision: 4),
            message: '---!---> Wrongly measured speed',
        );

        $this->assertEquals(
            expected: 15.3846,
            actual: round(num: $stat->getUploadSpeedFromReferencePoint(), precision: 4),
            message: '---!---> Wrongly measured speed',
        );

        $this->assertEquals(
            expected: 23.0769,
            actual: round(num: $stat->getTotalSpeedFromReferencePoint(), precision: 4),
            message: '---!---> Wrongly measured speed',
        );

        $this->assertEquals(
            expected: 14501,
            actual: $stat->getTrafficLeft(),
            message: '---!---> Wrongly measured traffic left',
        );

        $this->assertEquals(
            expected: 0.1678,
            actual: round(num: $stat->getTransferRateLeft(), precision: 4),
            message: '---!---> Wrongly measured traffic left',
        );

        // Humanisation
        $this->assertEquals(
            expected: '23.08 B/s',
            actual: $stat->getTotalSpeedFromReferencePointReadable(),
            message: '---!---> Problem with human-readable conversion',
        );

        $this->assertEquals(
            expected: '14.16 kB',
            actual: $stat->getTrafficLeftReadable(),
            message: '---!---> Problem with human-readable conversion',
        );

        $this->assertEquals(
            expected: '7 B/s',
            actual: $stat->getDownloadSpeedFromReferencePointReadable(),
            message: '---!---> Problem with human-readable conversion',
        );

        $this->assertEquals(
            expected: '15 B/s',
            actual: $stat->getUploadSpeedFromReferencePointReadable(),
            message: '---!---> Problem with human-readable conversion',
        );

        $this->assertEquals(
            expected: '300 B',
            actual: $stat->getTotalTrafficFromReferencePointReadable(),
            message: '---!---> Problem with human-readable conversion',
        );

        $this->assertEquals(
            expected: '0 B/s',
            actual: $stat->getTransferRateLeftReadable(),
            message: '---!---> Problem with human-readable conversion',
        );
    }
}
