<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RedditChannel;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedditChannel|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedditChannel|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedditChannel[]    findAll()
 * @method RedditChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class RedditChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedditChannel::class);
    }

    public function getMyChannels(User $user, string $filter)
    {
        $qb = $this->createQueryBuilder('c')
            ->distinct(true);
        $nsfw = ($filter === 'nsfw');
        if (false === $nsfw) {
            $qb->andWhere('c.nsfw = :nsfw')
                ->setParameter('nsfw', $nsfw);
        }
        $qb->orderBy('c.name', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
