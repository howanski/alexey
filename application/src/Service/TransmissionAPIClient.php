<?php

declare(strict_types=1);

namespace App\Service;

use LogicException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class TransmissionAPIClient
{
    private bool $isConfigured = false;

    private int $port;

    private string $auth;

    private string $host;

    private string $rpcPath;

    private string $scheme;

    private string $sessionId = '';

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function configureEndpoint(
        string $host,
        string $password,
        string $username,
        int $port = 9091,
        string $rpcPath = '/transmission/rpc/',
        string $scheme = 'http',
    ): void {
        $this->auth = base64_encode($username . ':' . $password);
        $this->host = $host;
        $this->port = $port;
        $this->rpcPath = $rpcPath;
        $this->scheme = $scheme;
        $this->isConfigured = true;
    }

    public function setDownloadSpeedLimit(int $kBps): void
    {

        $this->checkConfig();

        $this->ensureSessionSet();

        $upLimit = (int) ($kBps / 10);
        if ($upLimit < 5) {
            $upLimit = 5;
        }

        $this->callRpc(
            body: $this->constructBody(
                methodName: 'session-set',
                arguments: [
                    'alt-speed-down' => $kBps,
                    'speed-limit-down-enabled' => true,
                    'speed-limit-down' => $kBps,
                    'speed-limit-up-enabled' => true,
                    'speed-limit-up' => $upLimit,
                ]
            ),
        );
    }

    private function ensureSessionSet(): void
    {
        if ($this->sessionId != '') {
            return;
        }
        $response = $this->callRpc();
        $headers = $response->getHeaders(false);
        $this->sessionId = $headers['x-transmission-session-id'][0];
    }

    private function constructBody(string $methodName, array $arguments): string
    {
        return json_encode(['method' => $methodName, 'arguments' => $arguments]);
    }

    private function callRpc(string $body = ''): ResponseInterface
    {

        $options = [
            'headers' => [
                'Authorization' => sprintf('Basic %s', $this->auth),
                'X-Transmission-Session-Id' => $this->sessionId,
            ],
        ];

        if (strlen($body) > 0) {
            $options['body'] = $body;
        }

        $response = $this->client->request(
            method: 'POST',
            url: $this->getUrl(),
            options: $options,
        );

        return $response;
    }

    private function getUrl(): string
    {
        return $this->scheme . '://' . $this->host . ':' . $this->port . $this->rpcPath;
    }

    private function checkConfig(): void
    {
        if (false === $this->isConfigured) {
            throw new LogicException('TransmissionAPIClient: configureEndpoint() was not called');
        }
    }
}
