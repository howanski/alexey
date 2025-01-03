<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiDevice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApiDevice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiDevice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiDevice[]    findAll()
 * @method ApiDevice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ApiDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiDevice::class);
    }

    public function getMyDevices(User $user): array
    {
        return $this->createQueryBuilder('ad')
            ->andWhere('ad.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ad.lastRequest', 'DESC')
            ->setCacheable(true)
            ->setCacheRegion('default')
            ->getQuery()
            ->getResult();
    }

    public function countMyDevices(User $user): int
    {
        return $this->count(
            criteria: [
                'user' => $user,
            ],
        );
    }
}
