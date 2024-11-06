<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\SimpleSetting;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method SimpleSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimpleSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimpleSetting[]    findAll()
 * @method SimpleSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class SimpleSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimpleSetting::class);
    }

    public function findAllByKeys($keys, $user): array
    {
        $qb = $this->createQueryBuilder('ss')
            ->andWhere('ss.settingKey IN (:val)')
            ->setParameter('val', $keys)
            ->setCacheable(true)
            ->setCacheRegion('default');
        if ($user instanceof User) {
            $qb->andWhere('ss.user = :user')
                ->setParameter('user', $user);
        }

        return $qb->getQuery()
            ->getResult();
    }
}
