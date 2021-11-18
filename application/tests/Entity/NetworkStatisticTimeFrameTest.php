<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\NetworkStatisticTimeFrame;

final class NetworkStatisticTimeFrameTest extends TestCase
{
    public function testGetBillingFrameEndReadable(): void
    {
        $entity = new NetworkStatisticTimeFrame();
        $twoHoursAgo = new \DateTime('now');
        $twoHours = new \DateInterval('PT2H');
        $twoHoursAgo->sub($twoHours);
        $entity->setBillingFrameEnd($twoHoursAgo);
        $this->assertEquals(
            expected: '2 hours ago',
            actual: $entity->getBillingFrameEndReadable('en'),
        );
    }

    public function testGetBillingFrameDataLimitReadable(): void
    {
        $entity = new NetworkStatisticTimeFrame();
        $entity->setBillingFrameDataLimit(intval(1024 * 1024 * 1024 * 2.5));
        $this->assertEquals(
            expected: '2.5 GB',
            actual: $entity->getBillingFrameDataLimitReadable(),
        );
    }
}
