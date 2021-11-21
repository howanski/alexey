<?php

declare(strict_types=1);

namespace App\Class;

use Symfony\Component\HttpFoundation\JsonResponse;

final class DynamicCard
{
    private string $headerText = '';
    private string $headerValue = '';
    private string $footerValue = '';
    private bool $isRaw = false;
    private string $rawContent = '';

    public function toResponse(): JsonResponse
    {
        $data = [
            'headerText' => $this->headerText,
            'headerValue' => $this->headerValue,
            'footerValue' => $this->footerValue,
            'isRaw' => $this->isRaw,
            'rawContent' => base64_encode(string: $this->rawContent),
        ];

        return new JsonResponse($data);
    }

    public function setHeaderText(string $headerText)
    {
        $this->headerText = $headerText;
        return $this;
    }

    public function setHeaderValue(string $headerValue)
    {
        $this->headerValue = $headerValue;
        return $this;
    }

    public function setFooterValue(string $footerValue)
    {
        $this->footerValue = $footerValue;
        return $this;
    }

    public function setRawContent(string $rawContent): self
    {
        $this->isRaw = true;
        $this->rawContent = $rawContent;
        return $this;
    }
}
