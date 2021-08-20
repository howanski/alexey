<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NetworkStatisticTimeFrame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NetworkStatisticTimeFrame|null find($id, $lockMode = null, $lockVersion = null)
 * @method NetworkStatisticTimeFrame|null findOneBy(array $criteria, array $orderBy = null)
 * @method NetworkStatisticTimeFrame[]    findAll()
 * @method NetworkStatisticTimeFrame[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NetworkStatisticTimeFrameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NetworkStatisticTimeFrame::class);
    }
}
