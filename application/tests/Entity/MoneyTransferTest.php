<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\MoneyNode;
use App\Entity\MoneyTransfer;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class MoneyTransferTest extends TestCase
{
    public function testEntityFields(): void
    {
        $user  = new User();
        $entity = new MoneyTransfer($user);

        $today = new \DateTime('today');
        $src = new MoneyNode($user);
        $target = new MoneyNode($user);

        $entity
            ->setSourceNode($src)
            ->setTargetNode($target)
            ->setOperationDate($today)
            ->setOperationDateString($today->format('d.m.Y'))
            ;

        $this->assertEquals(expected: $today, actual: $entity->getOperationDate());
        $this->assertEquals(expected: $src, actual: $entity->getSourceNode());
        $this->assertEquals(expected: $target, actual: $entity->getTargetNode());
        $this->assertEquals(expected: $today->format('d.m.Y'), actual: $entity->getOperationDateString());

        $this->assertEquals(expected: null, actual: $entity->getId());
    }
}
