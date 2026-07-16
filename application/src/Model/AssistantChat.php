<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\AssistantCall;

final class AssistantChat
{
    private array $messages = [];
    public static function fromCall(AssistantCall $call): static
    {
        if ($call->getRoot() instanceof AssistantCall) {
            return self::fromCall($call->getRoot());
        }
        $dto = new self();
        $dto->setMessages($call->getMessagesTimeline());
        return $dto;
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
