<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AssistantCall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<AssistantCall>
 */
final class AssistantCallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssistantCall::class);
    }

    /**
     * @return AssistantCall[]
     */
    public function getUserChats(UserInterface $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.type = :chatType')
            ->andWhere('c.root IS NULL')
            ->orderBy('c.lastStatusChange', 'DESC')
            ->setParameters([
                'user' => $user,
                'chatType' => AssistantCall::TYPE_CHAT,
            ])
            ->getQuery()
            ->getResult();
    }

    public function countCallsWithStatus(int $status): int
    {
        return $this->count(
            criteria: [
                'status' => $status,
            ],
        );
    }

    public function findOldestWithStatus(int $status): ?AssistantCall
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.lastStatusChange', 'ASC')
            ->andWhere('c.status = :status')
            ->setParameters([
                'status' => $status,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
