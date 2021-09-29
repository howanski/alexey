<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AsyncJob;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AsyncJobHandler implements MessageHandlerInterface
{
    public function __invoke(AsyncJob $message)
    {
    }
}
