<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RedditBannedPoster;
use App\Entity\RedditChannel;
use App\Entity\RedditPost;
use App\Entity\User;
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

    public function dropBannedPosterPosts(User $user, string $username): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql =
            'DELETE reddit_post ' .
            'FROM reddit_post ' .
            'JOIN reddit_channel rchnl ON rchnl.id = reddit_post.channel_id ' .
            'WHERE rchnl.user_id = :user ' .
            'AND reddit_post.user = :username;';
        $count = $connection->executeStatement(
            sql: $sql,
            params: [
                'user' => $user->getId(),
                'username' => $username,
            ],
        );
        return $count;
    }

    public function getUnseen(RedditChannel $channel, int $limit = 100): array
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
