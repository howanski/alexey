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

    public function cleanup(): int
    {
        $oldTime = date('Y-m-d', strtotime('-3 months'));
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql =
            'DELETE reddit_channel ' .
            'FROM reddit_channel ' .
            'LEFT JOIN reddit_post ON reddit_channel.id = reddit_post.channel_id ' .
            'WHERE reddit_channel.last_fetch < :oldTime ' .
            'AND reddit_post.id IS NULL;';
        $count = $connection->executeStatement(
            sql: $sql,
            params: [
                'oldTime' => $oldTime,
            ],
        );

        $cache = $em->getCache();
        $cache->evictEntityRegion(RedditChannel::class);

        return (int) $count;
    }

    public function getMyChannels(User $user, string $filter): array
    {
        $qb = $this->createQueryBuilder('c')
            ->distinct(true);
        $qb->andWhere('c.user = :user')
            ->setParameter('user', $user);
        $qb->leftJoin('c.channelGroup', 'channelGroup');
        if ($filter === '*') {
            $qb->andWhere('channelGroup IS NULL');
        } else {
            $qb->andWhere('channelGroup.name = :groupName')
                ->setParameter('groupName', $filter);
        }
        $qb->orderBy('c.name', 'ASC');
        $qb->setCacheable(true)
            ->setCacheRegion('default');

        return $qb->getQuery()->getResult();
    }
}
