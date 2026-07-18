<?php

declare(strict_types=1);

namespace App\AssistantTool;

use App\Service\WeatherService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

// https://ai.symfony.com/cookbook/tool-calling-with-agents
#[AsTool('weather', 'Fetches the current and future weather in user location')]
final class WeatherTool
{
    public function __construct(
        private WeatherService $weatherService,
    ) {
    }

    /**
     * @param string $type "daily" or "hourly"
     */
    public function __invoke(string $type = 'daily'): array
    {
        return $this->weatherService->getChartData(locale: 'en', type: $type);
    }
}