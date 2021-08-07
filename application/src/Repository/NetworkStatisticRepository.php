<?php

namespace App\Repository;

use App\Entity\NetworkStatistic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NetworkStatistic|null find($id, $lockMode = null, $lockVersion = null)
 * @method NetworkStatistic|null findOneBy(array $criteria, array $orderBy = null)
 * @method NetworkStatistic[]    findAll()
 * @method NetworkStatistic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NetworkStatisticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NetworkStatistic::class);
    }
}
