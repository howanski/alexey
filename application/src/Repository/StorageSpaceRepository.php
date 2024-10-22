<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StorageSpace;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StorageSpace|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageSpace|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageSpace[]    findAll()
 * @method StorageSpace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class StorageSpaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StorageSpace::class);
    }

    public function getFindByUserBuilder(User $value): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.user = :val')
            ->setParameter('val', $value)
            ->orderBy('s.name', 'ASC');
        return $qb;
    }

    public function findByUser(User $value): array
    {
        return $this->getFindByUserBuilder($value)
            ->getQuery()
            ->getResult()
        ;
    }
}
