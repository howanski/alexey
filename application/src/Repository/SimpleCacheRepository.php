<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SimpleCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SimpleCache|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimpleCache|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimpleCache[]    findAll()
 * @method SimpleCache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class SimpleCacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimpleCache::class);
    }

    public function findValidRecordByKey(string $key)
    {
        $now = new \DateTime('now');
        return $this->createQueryBuilder('s')
            ->andWhere('s.cacheKey = :key')
            ->setParameter('key', $key)
            ->andWhere('s.validTo > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRecordByKey(string $key)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.cacheKey = :key')
            ->setParameter('key', $key)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
