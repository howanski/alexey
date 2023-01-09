<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RedditBannedPoster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedditBannedPoster|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedditBannedPoster|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedditBannedPoster[]    findAll()
 * @method RedditBannedPoster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class RedditBannedPosterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedditBannedPoster::class);
    }
}
