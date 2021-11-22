<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AsyncJob;
use App\Service\NetworkUsageService;
use App\Service\TransmissionService;
use App\Service\NetworkMachineService;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AsyncJobHandler implements MessageHandlerInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private NetworkMachineService $networkMachineService,
        private NetworkUsageService $networkUsageService,
        private TransmissionService $transmissionService,
    ) {
    }

    public function __invoke(AsyncJob $message)
    {
        $jobType = $message->getJobType();
        $payload = $message->getPayload();

        switch ($jobType) {
            case AsyncJob::TYPE_PING_ALL_MACHINES:
                $this->networkMachineService->schedulePinging();
                break;
            case AsyncJob::TYPE_PING_MACHINE:
                $this->networkMachineService->pingNetworkMachine(id: $payload['id']);
                break;
            case AsyncJob::TYPE_UPDATE_NETWORK_STATS:
                $this->networkUsageService->updateStats();
                $this->bus->dispatch(new AsyncJob(
                    jobType: AsyncJob::TYPE_TRANSMISSION_SPEED_ADJUST,
                    payload: [],
                ));
                break;
            case AsyncJob::TYPE_TRANSMISSION_SPEED_ADJUST:
                $this->transmissionService->adjustSpeed();
                break;
            case AsyncJob::TYPE_CLEANUP_NETWORK_STATS:
                $this->networkUsageService->cleanUpStats();
                break;
            case AsyncJob::TYPE_WAKE_ON_LAN:
                $this->networkMachineService->wakeOnLan(
                    wakeDestination: $payload['wakeDestination'],
                    macAddress: $payload['macAddress'],
                );
                break;
        }
    }
}
