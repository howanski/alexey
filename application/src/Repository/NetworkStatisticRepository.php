<?php

namespace App\Repository;

use App\Entity\NetworkStatistic;
use DateTime;
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

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return NetworkStatistic[]
     */
    public function getOrderedFromTimeRange(DateTime $from, DateTime $to): array
    {
        $qb = $this->createQueryBuilder('ns')
            ->andWhere('ns.probingTime > :from')
            ->setParameter('from', $from)
            ->andWhere('ns.probingTime < :to')
            ->setParameter('to', $to)
            ->addOrderBy('ns.probingTime', 'ASC');
        $qb->distinct(true);
        return $qb->getQuery()->getResult();
    }

    public function getLatestOne(): NetworkStatistic
    {
        $qb = $this->createQueryBuilder('ns')
            ->addOrderBy('ns.id', 'DESC')
            ->setMaxResults(2);
        $qb->distinct(true);
        $result = $qb->getQuery()->getResult();
        if (is_array($result)) {
            if (array_key_exists(1, $result)) {
                $result[0]->setReferencePoint($result[1]);
            }
            if (array_key_exists(0, $result)) {
                $result = $result[0];
            }
        }

        return $result;
    }
}
