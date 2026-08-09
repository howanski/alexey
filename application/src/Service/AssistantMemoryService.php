<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AssistantMemory;
use App\Entity\AssistantRecurringMessage;
use App\Repository\AssistantMemoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AssistantMemoryService
{
    public function __construct(
        private AssistantMemoryRepository $memoryRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function addMemory(AssistantRecurringMessage $assistant, string $title, string $content): AssistantMemory
    {
        // Check for duplicate content among active memories
        $existing = $this->memoryRepository->findByContent($assistant, $content);
        if ($existing instanceof AssistantMemory) {
            return $existing;
        }

        $memory = AssistantMemory::createNew($assistant, $title, $content);

        $this->em->persist($memory);
        $this->em->flush();
        $this->em->refresh($memory);

        return $memory;
    }

    public function getMemoryForAssistant(AssistantRecurringMessage $assistant, int $id): ?AssistantMemory
    {
        return $this->memoryRepository->findOneBy(['id' => $id, 'assistant' => $assistant]);
    }

    public function editMemory(
        AssistantRecurringMessage $assistant,
        int $memoryId,
        string $newTitle,
        string $newContent,
    ): ?AssistantMemory {
        $memory = $this->getMemoryForAssistant($assistant, $memoryId);
        if (!$memory instanceof AssistantMemory) {
            return null;
        }

        $memory->setAssistantTitle($newTitle);
        $memory->setAssistantContent($newContent);
        $memory->setStatus(AssistantMemory::STATUS_EDITED_BY_ASSISTANT);
        $this->em->flush();
        $this->em->refresh($memory);

        return $memory;
    }

    public function markMemoryDeleted(AssistantRecurringMessage $assistant, int $memoryId): ?AssistantMemory
    {
        $memory = $this->getMemoryForAssistant($assistant, $memoryId);
        if (!$memory instanceof AssistantMemory) {
            return null;
        }

        $memory->setStatus(AssistantMemory::STATUS_DELETED_BY_ASSISTANT);
        $memory->setAssistantContent('');
        $memory->setAssistantTitle('');
        $this->em->flush();
        $this->em->refresh($memory);

        return $memory;
    }

    public function confirmMemory(UserInterface $user, int $memoryId): bool
    {
        $memory = $this->memoryRepository->find($memoryId);
        if (!$memory instanceof AssistantMemory) {
            return false;
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            return false;
        }
        if (AssistantMemory::STATUS_RESOLVED_BY_USER === $memory->getStatus()) {
            return false;
        }

        if (AssistantMemory::STATUS_DELETED_BY_ASSISTANT === $memory->getStatus()) {
            $this->em->remove($memory);
        } else {
            $memory->acceptAssistantVersion();
        }

        $this->em->flush();
        return true;
    }

    public function rejectMemory(UserInterface $user, int $memoryId): bool
    {
        $memory = $this->memoryRepository->find($memoryId);
        if (!$memory instanceof AssistantMemory) {
            return false;
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            return false;
        }
        if (AssistantMemory::STATUS_RESOLVED_BY_USER === $memory->getStatus()) {
            return false;
        }

        if (AssistantMemory::STATUS_CREATED_BY_ASSISTANT === $memory->getStatus()) {
            $this->em->remove($memory);
        } else {
            $memory->acceptUserVersion();
        }

        $this->em->flush();
        return true;
    }

    /**
     * Get all memories that are visible from assistant perspective.
     */
    public function getMemoriesForAssistant(AssistantRecurringMessage $assistant): array
    {
        return $this->memoryRepository->getMemoriesForAssistant($assistant);
    }
}
