<?php

declare(strict_types=1);

namespace App\Repository;

use DateTime;
use Carbon\Carbon;
use App\Entity\NetworkStatistic;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method NetworkStatistic|null find($id, $lockMode = null, $lockVersion = null)
 * @method NetworkStatistic|null findOneBy(array $criteria, array $orderBy = null)
 * @method NetworkStatistic[]    findAll()
 * @method NetworkStatistic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class NetworkStatisticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NetworkStatistic::class);
    }

    /**
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

    public function getLatestOne()
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

        if ($result instanceof NetworkStatistic) {
            return $result;
        } else {
            return null;
        }
    }

    public function findObsolete(): array
    {
        $monthAgo = new Carbon('now');
        $monthAgo->subMonths(1);
        $qb = $this->createQueryBuilder('ns')
            ->andWhere('ns.probingTime < :monthAgo')
            ->setParameter('monthAgo', $monthAgo);
        return $qb->getQuery()->getResult();
    }
}
