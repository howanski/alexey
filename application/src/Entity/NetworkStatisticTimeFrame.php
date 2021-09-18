<?php

declare(strict_types=1);

namespace App\Entity;

use App\Class\HHelpers;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NetworkStatisticTimeFrameRepository;
use Carbon\Carbon;

#[ORM\Entity(repositoryClass: NetworkStatisticTimeFrameRepository::class)]
final class NetworkStatisticTimeFrame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    private DateTime $billingFrameStart;

    #[ORM\Column(type: 'datetime')]
    private DateTime $billingFrameEnd;

    #[ORM\Column(type: 'bigint')]
    private int $billingFrameDataLimit = 0;

    #[ORM\OneToMany(targetEntity: NetworkStatistic::class, mappedBy: 'timeFrame', orphanRemoval: true)]
    private $networkStatistics;

    public function __construct()
    {
        $this->networkStatistics = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBillingFrameStart(): DateTime
    {
        return $this->billingFrameStart;
    }

    public function setBillingFrameStart(DateTime $billingFrameStart): self
    {
        $this->billingFrameStart = $billingFrameStart;
        return $this;
    }

    public function getBillingFrameEnd(): DateTime
    {
        return $this->billingFrameEnd;
    }

    public function setBillingFrameEnd(DateTime $billingFrameEnd): self
    {
        $this->billingFrameEnd = $billingFrameEnd;
        return $this;
    }

    public function getBillingFrameDataLimit(): int
    {
        return $this->billingFrameDataLimit;
    }

    public function getBillingFrameDataLimitReadable(): string
    {
        return HHelpers::formatBytes($this->getBillingFrameDataLimit());
    }

    public function setBillingFrameDataLimit(int $billingFrameDataLimit): self
    {
        $this->billingFrameDataLimit = $billingFrameDataLimit;
        return $this;
    }

    public function getNetworkStatistics(): Collection
    {
        return $this->networkStatistics;
    }

    public function addNetworkStatistic(NetworkStatistic $networkStatistic): self
    {
        if (!$this->networkStatistics->contains($networkStatistic)) {
            $this->networkStatistics[] = $networkStatistic;
            $networkStatistic->setTimeFrame($this);
        }

        return $this;
    }

    public function removeNetworkStatistic(NetworkStatistic $networkStatistic): self
    {
        if ($this->networkStatistics->removeElement($networkStatistic)) {
            // set the owning side to null (unless already changed)
            if ($networkStatistic->getTimeFrame() === $this) {
                $networkStatistic->setTimeFrame(null);
            }
        }

        return $this;
    }

    public function getBillingFrameEndReadable(): string
    {
        $conventionalTime = $this->getBillingFrameEnd();
        $carbonised = new Carbon($conventionalTime);
        return $carbonised->diffForHumans();
    }
}
