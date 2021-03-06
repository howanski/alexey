<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RedditChannel;
use App\Entity\RedditPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedditPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedditPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedditPost[]    findAll()
 * @method RedditPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class RedditPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedditPost::class);
    }

    public function cleanup()
    {
        $oldTime = date('Y-m-d', strtotime('-1 month'));
        $connection = $this->getEntityManager()->getConnection();
        $sql =
            'DELETE reddit_post ' .
            'FROM reddit_post ' .
            'WHERE reddit_post.seen = :seen ' .
            'AND reddit_post.touched < :ago;';
        $count = $connection->executeStatement(
            sql: $sql,
            params: [
                'seen' => true,
                'ago' => $oldTime,
            ],
        );
        return $count;
    }

    public function getUnseen(RedditChannel $channel, int $limit = 100)
    {
        return $this->createQueryBuilder('rp')
            ->andWhere('rp.channel = :channel')
            ->setParameter('channel', $channel)
            ->andWhere('rp.seen = :notseen')
            ->setParameter('notseen', false)
            ->setMaxResults($limit)
            ->addOrderBy('rp.published', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
