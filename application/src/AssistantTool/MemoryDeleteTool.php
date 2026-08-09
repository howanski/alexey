<?php

declare(strict_types=1);

namespace App\AssistantTool;

use App\Entity\AssistantRecurringMessage;
use App\Service\AssistantMemoryService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('memory_delete', 'Removes a stored fact(s).')]
final class MemoryDeleteTool
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
        $memory = $this->memoryService->markMemoryDeleted($this->assistant, $memoryId);
        if (null === $memory) {
            return [
                'error' => "Memory #$memoryId not found.",
            ];
        }

        return [
            'status' => 'deleted',
        ];
    }
}
