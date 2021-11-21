<?php

declare(strict_types=1);

namespace App\Class;

use Symfony\Component\HttpFoundation\JsonResponse;

final class DynamicCard
{
    public function __construct(
        private string $headerText,
        private string $headerValue,
        private string $footerValue,
    ) {
    }

    public function toResponse(): JsonResponse
    {
        $data = [
            'headerText' => $this->headerText,
            'headerValue' => $this->headerValue,
            'footerValue' => $this->footerValue,
        ];

        return new JsonResponse($data);
    }
}
