<?php

declare(strict_types=1);

namespace App\AssistantTool;

use App\Entity\AssistantMemory;
use App\Entity\AssistantRecurringMessage;
use App\Service\AssistantMemoryService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('memory_update', 'Updates an existing stored fact.')]
final class MemoryEditTool
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

    public function __invoke(int $memoryId, string $newTitle, string $newContent): array
    {

        $newTitle = trim($newTitle);
        $newContent = trim($newContent);

        if (strlen($newTitle) === 0) {
            return [
                'error' => 'Title is empty.',
            ];
        }

        if (strlen($newContent) === 0) {
            return [
                'error' => 'Content is empty.',
            ];
        }

        if (str_word_count($newTitle) > AssistantMemory::TITLE_WORD_LIMIT) {
            return [
                'error' => 'Title is too long. Max words accepted: ' . AssistantMemory::TITLE_WORD_LIMIT,
            ];
        }


        $memory = $this->memoryService->editMemory($this->assistant, $memoryId, $newTitle, $newContent);
        if (null === $memory) {
            return [
                'error' => "Memory #$memoryId not found.",
            ];
        }

        return [
            'status' => 'updated',
            'id' => (int) $memory->getId(),
        ];
    }
}
