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

    public function findByUser(User $user): array
    {
        //TODO: implement via joins: item -> stack -> s. space -> user
        return [];
    }
}
