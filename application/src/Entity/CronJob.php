<?php

declare(strict_types=1);

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CronJob
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?Carbon $lastRun = null;

    #[ORM\Column(type: 'integer')]
    private int $runEvery; //seconds

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $jobType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastRun(): ?Carbon
    {
        return $this->lastRun;
    }

    public function setLastRun(Carbon $lastRun): self
    {
        $this->lastRun = $lastRun;
        return $this;
    }

    public function getRunEvery(): int
    {
        return $this->runEvery;
    }

    public function setRunEvery(int $runEvery): self
    {
        $this->runEvery = $runEvery;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function setJobType($jobType)
    {
        $this->jobType = $jobType;
        return $this;
    }
}
