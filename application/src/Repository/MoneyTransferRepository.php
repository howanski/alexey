<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\MoneyTransfer;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method MoneyTransfer|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoneyTransfer|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoneyTransfer[]    findAll()
 * @method MoneyTransfer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class MoneyTransferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyTransfer::class);
    }

    public function getAllUserTransfers(User $user): array
    {
        return $this->createQueryBuilder('mt')
            ->andWhere('mt.user = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('mt.operationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAllUserTransfersFromMonth(User $user, DateTime $fromMonth): array
    {
        $month = new DateInterval('P1M');
        $nextMonth = clone ($fromMonth);
        $nextMonth->add($month);

        return $this->createQueryBuilder('mt')
            ->andWhere('mt.user = :user')
            ->andWhere('mt.operationDate < :to')
            ->andWhere('mt.operationDate >= :from')
            ->setParameters([
                'user' => $user->getId(),
                'to' => $nextMonth,
                'from' => $fromMonth,
            ])
            ->orderBy('mt.operationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getUserTransferMonths(User $user): array
    {
        //TODO: raw query with grouping by DATE_FORMAT("m.operation_date", "%Y-%m")
        $results = $this->createQueryBuilder('mt')
            ->select('mt.operationDate')
            ->andWhere('mt.user = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('mt.operationDate', 'DESC')
            ->getQuery()
            ->getResult();

        $niceData = [];
        foreach ($results as $result) {
            $trivialForm = $result['operationDate']->format('Y-m');
            if (false === in_array(needle: $trivialForm, haystack: $niceData)) {
                $niceData[] = $trivialForm;
            }
        }
        return $niceData;
    }
}
