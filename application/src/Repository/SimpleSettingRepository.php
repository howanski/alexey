<?php

namespace App\Repository;

use App\Entity\SimpleSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SimpleSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimpleSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimpleSetting[]    findAll()
 * @method SimpleSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimpleSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimpleSetting::class);
    }

    public function findAllByKeys($keys): array
    {
        return $this->createQueryBuilder('ss')
            ->andWhere('ss.settingKey IN (:val)')
            ->setParameter('val', $keys)
            ->getQuery()
            ->getResult();
    }
}
