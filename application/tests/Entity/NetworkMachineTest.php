<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\NetworkMachine;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Entity\NetworkMachine
 */
final class NetworkMachineTest extends TestCase
{
    public function testEntityFields(): void
    {
        $entity = new NetworkMachine();

        $today = new \DateTime('today');

        $entity->setUri('uri')
            ->setName('name')
            ->setStatus(2)
            ->setLastSeen($today)
            ->setMacAddress('AABB')
            ->setWakeDestination('WAKE')
            ->setShowOnDashboard(true);

        $this->assertEquals(expected: 'uri', actual: $entity->getUri());

        $this->assertEquals(expected: 'name', actual: $entity->getName());

        $this->assertEquals(expected: 2, actual: $entity->getStatus());

        $this->assertEquals(expected: $today, actual: $entity->getLastSeen());

        $this->assertEquals(expected: 'AABB', actual: $entity->getMacAddress());

        $this->assertEquals(expected: 'WAKE', actual: $entity->getWakeDestination());

        $this->assertEquals(expected: true, actual: $entity->getShowOnDashboard());
    }
}
