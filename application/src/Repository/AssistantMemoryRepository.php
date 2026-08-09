<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AssistantMemory;
use App\Entity\AssistantRecurringMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<AssistantMemory>
 */
final class AssistantMemoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssistantMemory::class);
    }

    public function getMemoriesForAssistant(AssistantRecurringMessage $assistant): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.assistant = :assistant')
            ->andWhere('m.status NOT IN (:statuses)')
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'assistant' => $assistant,
                'statuses' => [
                    AssistantMemory::STATUS_DELETED_BY_ASSISTANT,
                ],
            ])
            ->getQuery()
            ->getResult();
    }

    public function getAllMemories(UserInterface $user): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.assistant', 'a')
            ->andWhere('a.user = :user')
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'user' => $user,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findByContent(AssistantRecurringMessage $assistant, string $content): ?AssistantMemory
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.assistant = :assistant')
            ->andWhere('m.assistantContent = :content')
            ->setMaxResults(1)
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'assistant' => $assistant,
                'content' => $content,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAssistantMemories(AssistantRecurringMessage $assistant): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->andWhere('m.assistant = :assistant')
            ->andWhere('m.status NOT IN (:statuses)')
            ->orderBy('m.createdAt', 'DESC')
            ->setParameters([
                'assistant' => $assistant,
                'statuses' => [
                    AssistantMemory::STATUS_DELETED_BY_ASSISTANT,
                ],
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
