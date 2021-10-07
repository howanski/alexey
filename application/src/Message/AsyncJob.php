<?php

declare(strict_types=1);

namespace App\Message;

final class AsyncJob
{
    public const TYPE_PING = 'ping';
    public const TYPE_UPDATE_NETWORK_STATS = 'update_network_stats';
    public const TYPE_TRANSMISSION_SPEED_ADJUST = 'transmission_speed_adjust';
    public const TYPE_CLEANUP_NETWORK_STATS = 'cleanup_network_stats';

    public function __construct(
        private string $jobType,
    ) {
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }
}
