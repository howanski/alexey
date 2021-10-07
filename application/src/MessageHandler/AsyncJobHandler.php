<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AsyncJob;
use App\Service\NetworkUsageService;
use App\Service\NetworkMachineService;
use App\Service\TransmissionService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AsyncJobHandler implements MessageHandlerInterface
{
    public function __construct(
        private NetworkMachineService $networkMachineService,
        private NetworkUsageService $networkUsageService,
        private TransmissionService $transmissionService,
    ) {
    }

    public function __invoke(AsyncJob $message)
    {
        if (AsyncJob::TYPE_PING === $message->getJobType()) {
            $this->networkMachineService->pingMachines();
        } elseif (AsyncJob::TYPE_UPDATE_NETWORK_STATS === $message->getJobType()) {
            $this->networkUsageService->updateStats();
        } elseif (AsyncJob::TYPE_TRANSMISSION_SPEED_ADJUST === $message->getJobType()) {
            $this->transmissionService->adjustSpeed();
        } elseif (AsyncJob::TYPE_CLEANUP_NETWORK_STATS === $message->getJobType()) {
            $this->networkUsageService->cleanUpStats();
        }
    }
}
