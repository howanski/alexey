<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\MoneyNode;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function getAllUserNodes(User $user, int $groupId = null)
    {
        $qb = $this->createQueryBuilder('mn')
            ->andWhere('mn.user = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('mn.name', 'ASC');
        if (false === is_null($groupId)) {
            $qb->andWhere('mn.nodeGroup = :groupId')
                ->setParameter('groupId', $groupId);
        }
        return $qb
            ->getQuery()
            ->getResult();
    }
}
