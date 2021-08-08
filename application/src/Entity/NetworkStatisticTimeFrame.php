<?php

namespace App\Entity;

use App\Class\HHelpers;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NetworkStatisticTimeFrameRepository;

/**
 * @ORM\Entity(repositoryClass=NetworkStatisticTimeFrameRepository::class)
 */
class NetworkStatisticTimeFrame
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $billingFrameStart;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $billingFrameEnd;

    /**
     * @ORM\Column(type="bigint")
     * @var int
     */
    private $billingFrameDataLimit = 0;

    /**
     * @ORM\OneToMany(targetEntity=NetworkStatistic::class, mappedBy="timeFrame", orphanRemoval=true)
     */
    private $networkStatistics;

    public function __construct()
    {
        $this->networkStatistics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return  DateTime
     */
    public function getBillingFrameStart()
    {
        return $this->billingFrameStart;
    }

    /**
     * @param  DateTime  $billingFrameStart
     *
     * @return  self
     */
    public function setBillingFrameStart(DateTime $billingFrameStart)
    {
        $this->billingFrameStart = $billingFrameStart;

        return $this;
    }

    /**
     * @return  DateTime
     */
    public function getBillingFrameEnd()
    {
        return $this->billingFrameEnd;
    }

    /**
     * @param  DateTime  $billingFrameEnd
     *
     * @return  self
     */
    public function setBillingFrameEnd(DateTime $billingFrameEnd)
    {
        $this->billingFrameEnd = $billingFrameEnd;

        return $this;
    }

    /**
     * @return  int
     */
    public function getBillingFrameDataLimit()
    {
        return $this->billingFrameDataLimit;
    }


    /**
     * @return  string
     */
    public function getBillingFrameDataLimitReadable(): string
    {
        return HHelpers::formatBytes($this->getBillingFrameDataLimit());
    }

    /**
     * @param  int  $billingFrameDataLimit
     *
     * @return  self
     */
    public function setBillingFrameDataLimit(int $billingFrameDataLimit)
    {
        $this->billingFrameDataLimit = $billingFrameDataLimit;

        return $this;
    }

    /**
     * @return Collection|NetworkStatistic[]
     */
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
}
