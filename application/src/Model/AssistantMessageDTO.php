<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\AssistantService;

final class AssistantMessageDTO
{
    private string $message;

    private int $modelId = 0;

    private ?int $rootId = null;

    private array $tools = AssistantService::TOOLS_AVAILABLE;

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getModelId(): int
    {
        return $this->modelId;
    }

    public function setModelId(int $modelId): self
    {
        $this->modelId = $modelId;
        return $this;
    }

    public function getRootId(): ?int
    {
        return $this->rootId;
    }

    public function setRootId(?int $rootId): self
    {
        $this->rootId = $rootId;
        return $this;
    }

    public function getTools(): array
    {
        return $this->tools;
    }

    public function setTools(?array $tools): self
    {
        if (empty($tools)) {
            $this->tools = [];
        } else {
            $this->tools = $tools;
        }
        return $this;
    }
}
