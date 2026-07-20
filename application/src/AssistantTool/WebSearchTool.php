<?php

declare(strict_types=1);

namespace App\AssistantTool;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// https://ai.symfony.com/cookbook/tool-calling-with-agents
#[AsTool('web_search', 'Fetches urls with partial content from the web that match query')]
final class WebSearchTool
{
    private const DEFAULT_SEARCH_URL = 'http://searxng:8080/search';

    public function __construct(
        private HttpClientInterface $httpClient,
        private ?LoggerInterface $logger = null,
        private ?string $searchUrl = null,
    ) {
    }

    /**
     * @param string $query search query
     * @param string $language BCP 47 language code (e.g. "en", "pl") — defaults to "en"
     */
    public function __invoke(string $query, string $language = 'en'): array
    {
        try {
            $baseUrl = $this->searchUrl ?? self::DEFAULT_SEARCH_URL;
            $query = urlencode($query);
            $api = $baseUrl . '?q=' . $query . '&format=json&language=' . urlencode($language);

            $resultJson = $this->httpClient
                ->request(method: 'GET', url: $api, options: ['timeout' => 30])
                ->getContent();
            $result = json_decode($resultJson, true);

            if ($result === null) {
                return [
                    'error' => 'Invalid JSON response from SearXNG.',
                ];
            }

            $result = $this->sanitize($result);
        } catch (Exception $e) {
            $message = "Error during web search: " . $e->getMessage();
            if (!is_null($this->logger)) {
                $this->logger->error($message, ['query' => $query, 'exception' => $e]);
            }
            return [
                'error' => $message,
            ];
        }

        return $result;
    }

    private function sanitize(array $input): array
    {
        // Intentionally dropping info that fill in Agent's context without adding value
        $usefulFields = [
            'title' => 'string',
            'content' => 'string',
            'url' => 'string',
        ];
        $output = [
            'query' => $input['query'] ?? '',
            'results' => [],
        ];

        foreach ($input['results'] as $result) {
            $subResult = [];
            foreach ($result as $resultKey => $val) {
                if (array_key_exists($resultKey, $usefulFields)) {
                    $subResult[$resultKey] = $val;
                }
            }
            $output['results'][] = $subResult;
        }

        return $output;
    }
}
