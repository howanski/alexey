<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AssistantRecurringMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<AssistantRecurringMessage>
 */
final class AssistantRecurringMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssistantRecurringMessage::class);
    }

    /**
     * @return AssistantRecurringMessage|null
     */
    public function getDefaultSystemMessage(UserInterface $user): ?AssistantRecurringMessage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.type = :type')
            ->orderBy('c.priority', 'ASC')
            ->setParameters([
                'user' => $user,
                'type' => AssistantRecurringMessage::TYPE_SYSTEM_MESSAGE,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
