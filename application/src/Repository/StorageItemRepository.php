<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StorageItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StorageItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageItem[]    findAll()
 * @method StorageItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class StorageItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StorageItem::class);
    }

    public function findByUser(
        User $user,
        int $storageSpaceId = 0,
        int $storageItemId = 0,
        bool $withMinimalQuantity = false,
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.stacks', 'storageStack')
            ->innerJoin('storageStack.storageSpace', 'storageSpace')
            ->andWhere('storageSpace.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.name', 'ASC');

        if ($storageSpaceId > 0) {
            $qb->andWhere('storageSpace.id = :storageSpaceId')
                ->setParameter('storageSpaceId', $storageSpaceId);
        }

        if ($storageItemId > 0) {
            $qb->andWhere('s.id = :storageItemId')
                ->setParameter('storageItemId', $storageItemId);
        }

        if (true === $withMinimalQuantity) {
            $qb->andWhere('s.minimalQuantity > 0');
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
