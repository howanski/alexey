<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AsyncJob;
use App\Service\NetworkUsageService;
use App\Service\NetworkMachineService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AsyncJobHandler implements MessageHandlerInterface
{
    public function __construct(
        private NetworkMachineService $networkMachineService,
        private NetworkUsageService $networkUsageService,
    ) {
    }

    public function __invoke(AsyncJob $message)
    {
        if (AsyncJob::TYPE_PING === $message->getJobType()) {
            $this->networkMachineService->pingMachines();
        } elseif (AsyncJob::TYPE_UPDATE_NETWORK_STATS === $message->getJobType()) {
            $this->networkUsageService->updateStats();
        }
    }
}
