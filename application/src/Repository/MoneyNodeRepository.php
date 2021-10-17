<?php

namespace App\Repository;

use App\Entity\MoneyNode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MoneyNode|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoneyNode|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoneyNode[]    findAll()
 * @method MoneyNode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoneyNodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyNode::class);
    }
}
