<?php

declare(strict_types=1);

namespace App\AssistantTool;

use DateTime;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

// https://ai.symfony.com/cookbook/tool-calling-with-agents
#[AsTool('datetime', 'Fetches current date and time')]
final class DateTimeTool
{
    public function __invoke(): array
    {
        $now = new DateTime('now');
        return [
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i:s'),
            'day_of_week' => $now->format('D'),
        ];
    }
}
