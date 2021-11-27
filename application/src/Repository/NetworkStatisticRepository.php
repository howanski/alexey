<?php

declare(strict_types=1);

namespace App\Repository;

use DateTime;
use Carbon\Carbon;
use App\Entity\NetworkStatistic;
use App\Entity\NetworkStatisticTimeFrame;
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

    public function dropObsoleteRecords(): int
    {
        $latestStat = $this->getLatestOne();
        if ($latestStat instanceof NetworkStatistic) {
            $timeFrame = $latestStat->getTimeFrame();
            if ($timeFrame instanceof NetworkStatisticTimeFrame) {
                $connection = $this->getEntityManager()->getConnection();
                $sql =
                    'DELETE network_statistic ' .
                    'FROM network_statistic ' .
                    'JOIN network_statistic_time_frame nstf ON nstf.id = network_statistic.time_frame_id ' .
                    'WHERE nstf.id != :id;';
                $count = $connection->executeStatement(
                    sql: $sql,
                    params: [
                        'id' => $timeFrame->getId(),
                    ],
                );
                return $count;
            }
        }
        return 0;
    }
}
