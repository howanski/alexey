<?php

declare(strict_types=1);

namespace App\Message;

class AsyncJob
{
    public function __construct(
        private string $jobType,
    ) {
    }
}
