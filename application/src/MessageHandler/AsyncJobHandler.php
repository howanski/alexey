<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AsyncJob;
use App\Service\NetworkMachineService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AsyncJobHandler implements MessageHandlerInterface
{
    public function __construct(
        private NetworkMachineService $networkMachineService,
    ) {
    }

    public function __invoke(AsyncJob $message)
    {
        if (AsyncJob::TYPE_PING === $message->getJobType()) {
            $this->networkMachineService->pingMachines();
        }
    }
}
