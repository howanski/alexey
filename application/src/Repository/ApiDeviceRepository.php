<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiDevice;
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
}
