<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\MessageInterface;

final class AssistantMessageBag
{
    private $messages = [];

    public function addMessage(MessageInterface $message): void
    {
        $this->messages[] = $message;
    }

    public function getBag(): MessageBag
    {
        return new MessageBag(
            ...$this->messages
        );
    }
}
