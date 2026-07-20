<?php

declare(strict_types=1);

namespace App\AssistantTool;

use Exception;
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\HtmlConverterInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// https://ai.symfony.com/cookbook/tool-calling-with-agents
#[AsTool('url_read', 'Fetches url in readable md format')]
final class ReadUrlTool
{
    private HtmlConverterInterface $converter;

    public function __construct(
        private HttpClientInterface $httpClient,
        private ?LoggerInterface $logger = null,
    ) {
        $this->converter = new HtmlConverter([
            'hard_break' => true,
            'strip_tags' => true,
            'remove_nodes' => 'head style',
        ]);
    }

    /**
     * @param string $url url of webpage to fetch
     * @param string $format "md" or "html"
     */
    public function __invoke(string $url, string $format = 'md'): string
    {
        if (!preg_match('#^https?://#i', $url)) {
            return "Error: Only http(s) URLs are allowed.";
        }

        $host = parse_url($url, PHP_URL_HOST);
        if ($host === false || $host === '') {
            return "Error: Invalid URL – could not extract host.";
        }

        // Normalise hostname for consistent checks
        $host = strtolower((string) $host);

        if (in_array($host, ['localhost', '127.0.0.1'], true)) {
            return "Error: Access to internal addresses is not allowed.";
        }

        // Resolve ALL A/AAAA records and verify every IP is public.
        // This prevents rebinding via a single gethostbyname call that
        // may only see one IP while the HTTP client connects to another.
        $dnsError = $this->validateHostIps($host);
        if (!is_null($dnsError)) {
            return $dnsError;
        }

        try {
            $response = $this->httpClient
                ->request(method: 'GET', url: $url, options: ['timeout' => 30]);

            // Post-request DNS resolution — detect delayed rebinding
            // (between the pre-check and this request the domain may have been re-resolved).
            // Re-verify all IPs again so a mid-flight rebinding is caught.
            $dnsError = $this->validateHostIps($host);
            if (!is_null($dnsError)) {
                return $dnsError;
            }

            $result = $response->getContent();

            if ($format === 'md') {
                $result = $this->converter->convert($result);
            }
        } catch (Exception $e) {
            $message = "Error fetching '$url': " . $e->getMessage();
            if (!is_null($this->logger)) {
                $this->logger->error($message, ['exception' => $e]);
            }
            return $message;
        }

        return $result;
    }

    /**
     * Check whether an IP address is private / loopback / link-local / reserved.
     *
     * FILTER_FLAG_NO_PRIV_RANGE covers RFC 1918 + IPv6 ULA (fc00::/7).
     * FILTER_FLAG_NO_RES_RANGE covers link-local, loopback, multicast, and reserved.
     */
    private function isPrivateIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return true; // Not a valid IP — treat as potentially dangerous
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) === false;
    }

    /**
     * Resolve all A (IPv4) and AAAA (IPv6) records for a host.
     *
     * Returns an array of valid IP strings, or [] on failure.
     */
    private function resolveAllIps(string $host): array
    {
        $ips = [];
        foreach ([DNS_A, DNS_AAAA] as $type) {
            $records = dns_get_record($host, $type);
            if ($records === false || $records === []) {
                continue;
            }
            foreach ($records as $record) {
                if (!empty($record['ip'])) {
                    $ips[] = $record['ip'];
                }
            }
        }

        // Deduplicate while preserving order
        return array_values(array_unique($ips));
    }

    /**
     * Validate that every resolved IP for the given host is public (non-private, non-reserved).
     *
     * Returns null on success, or an error message string if the check fails.
     */
    private function validateHostIps(string $host): ?string
    {
        $ips = $this->resolveAllIps($host);

        if ($ips === []) {
            return "Error: DNS resolution failed for '{$host}'.";
        }

        foreach ($ips as $ip) {
            if ($this->isPrivateIp($ip)) {
                return "Error: Access to internal addresses is not allowed.";
            }
        }

        return null; // All IPs are public — safe to proceed
    }
}
