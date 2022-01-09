<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RedditChannelGroup;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedditChannelGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedditChannelGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedditChannelGroup[]    findAll()
 * @method RedditChannelGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class RedditChannelGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedditChannelGroup::class);
    }

    public function getMineBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('rcg')
            ->andWhere('rcg.user = :user')
            ->setParameter('user', $user)
            ->orderBy('rcg.orderNumber', 'ASC')
            ->addOrderBy('rcg.name', 'ASC');
    }

    public function getMine(User $user): array
    {
        return $this->getMineBuilder($user)
            ->getQuery()->getResult();
    }
}
