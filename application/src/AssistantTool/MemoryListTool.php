<?php

declare(strict_types=1);

namespace App\AssistantTool;

use App\Entity\AssistantRecurringMessage;
use App\Service\AssistantMemoryService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    'memory_list',
    'Lists all factual notes topics. ' .
    'Use this tool before making decisions that depend on known facts or user preferences. ' .
    'Use memory_view for full content.',
)]
final class MemoryListTool
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

    public function __invoke(): array
    {
        $memories = $this->memoryService->getMemoriesForAssistant($this->assistant);
        $result = [];
        foreach ($memories as $memory) {
            $result[] = [
                'id' => (int) $memory->getId(),
                'title' => $memory->getAssistantTitle()
            ];
        }

        return [
            'memories' => $result,
            'count' => count($result),
        ];
    }
}
