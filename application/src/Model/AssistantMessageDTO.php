<?php

declare(strict_types=1);

namespace App\Model;

final class AssistantMessageDTO
{
    private string $message;

    private string $model;

    private ?int $rootId = null;

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
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
}
