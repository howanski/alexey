<?php

declare(strict_types=1);

namespace App\Class;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponse
{
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
    ];

    public const UI_ELEMENT_TEXT = 'txt';
    public const UI_ELEMENT_BUTTON = 'btn';
    public const UI_ELEMENT_LINK = 'lnk';

    private int $code = 200;

    private int $autoRefresh = 0;

    private string $message = 'ok';

    private array $ui = [];

    public function __construct(
        private string $userLocale,
    ) {
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function setRefreshInSeconds(int $seconds): void
    {
        $this->autoRefresh = intval(1000 * $seconds);
    }

    public function addText(string $string): self
    {
        $this->ui[] = [
            'type' => self::UI_ELEMENT_TEXT,
            'value' => $string,
        ];

        return $this;
    }

    public function addSpacer(): self
    {
        return $this->addText(
            string: '                              '
        );
    }

    public function addHorizontalLine(): self
    {
        return $this->addText(string: '⎯⎯⎯⎯⎯⎯⎯⎯⎯⎯⎯⎯');
    }

    public function addButton(string $name, string $path): self
    {
        $this->ui[] = [
            'type' => self::UI_ELEMENT_BUTTON,
            'name' => $name,
            'path' => $path,
        ];

        return $this;
    }

    public function addLink(string $name, string $path): self
    {
        $this->ui[] = [
            'type' => self::UI_ELEMENT_LINK,
            'name' => $name,
            'path' => $path,
        ];

        return $this;
    }

    public function getUiContent(): array
    {
        return $this->ui;
    }

    public function getLocale(): string
    {
        return $this->userLocale;
    }

    public function getRefreshTime(): int
    {
        return $this->autoRefresh;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    public function toResponse(): JsonResponse
    {
        return new JsonResponse(
            data: [
                'code' => $this->getStatusCode(),
                'message' => $this->getMessage(),
                'autoRefresh' => $this->getRefreshTime(),
                'ui' => $this->getUiContent(),
                'loc' => $this->getLocale(),
            ],
            status: $this->getStatusCode(),
            headers: self::CORS_HEADERS,
        );
    }
}
