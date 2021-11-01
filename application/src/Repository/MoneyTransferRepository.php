<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\MoneyTransfer;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method MoneyTransfer|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoneyTransfer|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoneyTransfer[]    findAll()
 * @method MoneyTransfer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoneyTransferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyTransfer::class);
    }

    public function getAllUserTransfers(User $user)
    {
        return $this->createQueryBuilder('mt')
        ->andWhere('mt.user = :user')
        ->setParameter('user', $user->getId())
        ->orderBy('mt.operationDate', 'DESC')
        ->getQuery()
        ->getResult();
    }
}
