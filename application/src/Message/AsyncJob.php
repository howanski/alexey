<?php

declare(strict_types=1);

namespace App\Message;

final class AsyncJob
{
    public const TYPE_PING_ALL_MACHINES = 'ping';
    public const TYPE_PING_MACHINE = 'ping_machine';
    public const TYPE_UPDATE_NETWORK_STATS = 'update_network_stats';
    public const TYPE_TRANSMISSION_SPEED_ADJUST = 'transmission_speed_adjust';
    public const TYPE_CLEANUP_NETWORK_STATS = 'cleanup_network_stats';
    public const TYPE_WAKE_ON_LAN = 'wake_on_lan';

    public function __construct(
        private string $jobType,
        private array $payload,
    ) {
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
