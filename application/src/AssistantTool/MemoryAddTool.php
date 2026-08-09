<?php

declare(strict_types=1);

namespace App\AssistantTool;

use App\Entity\AssistantMemory;
use App\Entity\AssistantRecurringMessage;
use App\Repository\AssistantMemoryRepository;
use App\Service\AssistantMemoryService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    'memory_add',
    'Stores a factual note for future conversations. ' .
    'Use this tool to store info that you might need when they are not in your context window anymore.',
)]
final class MemoryAddTool
{
    private AssistantRecurringMessage $assistant;

    public function __construct(
        private AssistantMemoryService $memoryService,
        private AssistantMemoryRepository $memoryRepository,
    ) {
    }

    public function setAssistant(AssistantRecurringMessage $assistant): void
    {
        $this->assistant = $assistant;
    }

    /**
     * @param string $title Short title that will be displayed on list (list_memories)
     * @param string $content Fact(s) to store
     */
    public function __invoke(string $title, string $content): array
    {
        $title = trim($title);
        $content = trim($content);

        if (strlen($title) === 0) {
            return [
                'error' => 'Title is empty.',
            ];
        }

        if (strlen($content) === 0) {
            return [
                'error' => 'Content is empty.',
            ];
        }

        if (str_word_count($title) > AssistantMemory::TITLE_WORD_LIMIT) {
            return [
                'error' => 'Title is too long. Max words accepted: ' . AssistantMemory::TITLE_WORD_LIMIT,
            ];
        }


        $limit = $this->assistant->getMaxMemories();
        if ($limit > 0) {
            $count = $this->memoryRepository->countAssistantMemories($this->assistant);
            if ($count >= $limit) {
                return [
                    'error' => 'Memory limit full. Remove less important memories or update/consolidate existing.'
                ];
            }
        }

        $memory = $this->memoryService->addMemory($this->assistant, $title, $content);

        return [
            'id' => (int) $memory->getId(),
            'status' => 'created',
            'title' => $title,
        ];
    }
}
