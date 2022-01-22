<?php

declare(strict_types=1);

namespace App\Class;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponse
{
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
    ];

    private const UI_ELEMENT_TEXT = 'txt';
    private const UI_ELEMENT_BUTTON = 'btn';
    private const UI_ELEMENT_LINK = 'lnk';

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

    public function setRefreshInSeconds(int $seconds)
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
        return $this->addText('                              ');
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

    public function toResponse(): JsonResponse
    {
        return new JsonResponse(
            data: [
                'code' => $this->code,
                'message' => $this->message,
                'autoRefresh' => $this->autoRefresh,
                'ui' => $this->ui,
                'loc' => $this->userLocale
            ],
            status: $this->code,
            headers: self::CORS_HEADERS,
        );
    }
}
