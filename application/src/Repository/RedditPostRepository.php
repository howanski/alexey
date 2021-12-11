<?php

declare(strict_types=1);

namespace App\Repository;

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
}
