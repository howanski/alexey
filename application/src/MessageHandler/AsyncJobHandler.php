<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AsyncJob;
use App\Service\MikrotikService;
use App\Service\NetworkMachineService;
use App\Service\NetworkUsageService;
use App\Service\RedditReader;
use App\Service\TransmissionService;
use App\Service\TunnelInfoProvider;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class AsyncJobHandler
{
    public function __construct(
        private MessageBusInterface $bus,
        private MikrotikService $mikrotikService,
        private NetworkMachineService $networkMachineService,
        private NetworkUsageService $networkUsageService,
        private RedditReader $redditReader,
        private TransmissionService $transmissionService,
        private TunnelInfoProvider $tunnelInfoProvider,
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
                try {
                    $this->networkUsageService->updateStats();
                    $this->bus->dispatch(new AsyncJob(
                        jobType: AsyncJob::TYPE_TRANSMISSION_SPEED_ADJUST,
                        payload: [],
                    ));
                } catch (\Exception) {
                    // Ignore connectivity issues
                    // Don't retry router connection until next scheduled time
                    // Queueing too many messages leads to racing which generates below-zero speeds in log
                    //                                           based on which sub-process finishes first
                }
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
            case AsyncJob::TYPE_UPDATE_CRAWLER:
                $this->redditReader->refreshAllChannels();
                break;
            case AsyncJob::TYPE_UPDATE_CRAWLER_CHANNEL:
                $this->redditReader->refreshChannelById(id: $payload['id']);
                break;
            case AsyncJob::TYPE_CHECK_TUNNEL_CHANGE:
                $this->tunnelInfoProvider->reactOnChanges();
                break;
            case AsyncJob::TYPE_POWER_CYCLE_MIKROTIK_LTE:
                $this->mikrotikService->handlePowerCycle(currentStep: $payload['step']);
                break;
        }
    }
}
