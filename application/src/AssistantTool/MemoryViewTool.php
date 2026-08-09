<?php

declare(strict_types=1);

namespace App\AssistantTool;

use App\Entity\AssistantMemory;
use App\Entity\AssistantRecurringMessage;
use App\Service\AssistantMemoryService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('memory_view', 'View memory content.')]
final class MemoryViewTool
{
    private AssistantRecurringMessage $assistant;

    public function __construct(
        private AssistantMemoryService $memoryService,
    ) {
    }

    public function setAssistant(AssistantRecurringMessage $assistant): void
    {
        $this->assistant = $assistant;
    }

    /**
     * @param int $memoryId memory identifier
     */
    public function __invoke(int $memoryId): array
    {
        $memory = $this->memoryService->getMemoryForAssistant($this->assistant, $memoryId);
        if (!($memory instanceof AssistantMemory)) {
            return [
                'error' => "Memory #$memoryId not found.",
            ];
        }

        return [
            'id' => $memory->getId(),
            'title' => $memory->getAssistantTitle(),
            'content' => $memory->getAssistantContent(),
            'createdAt' => [
                'date' => $memory->getCreatedAt()->format('Y-m-d'),
                'time' => $memory->getCreatedAt()->format('H:i:s'),
                'day_of_week' => $memory->getCreatedAt()->format('D'),
            ],
        ];
    }
}
