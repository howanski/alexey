<?php

namespace App\Entity;

use App\Repository\NetworkMachineRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NetworkMachineRepository::class)
 */
class NetworkMachine
{
    public const STATUS_UNKNOWN = 0;
    public const STATUS_UNREACHABLE = 1;
    public const STATUS_REACHABLE = 2;


    private const STATUSES_READABLE = [
        self::STATUS_UNKNOWN => '?',
        self::STATUS_UNREACHABLE => 'Unreachable',
        self::STATUS_REACHABLE => 'Visible',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=23, nullable=true)
     * @link https://en.wikipedia.org/wiki/MAC_address
     */
    private $macAddress;

    /**
     * @ORM\Column(type="string", length=23, nullable=true)
     */
    private $wakeDestination;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastSeen;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $showOnDashboard = false;

    public function __construct()
    {
        $this->status = self::STATUS_UNKNOWN;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusReadable(): string
    {
        return self::STATUSES_READABLE[$this->getStatus()];
    }

    public function isReachable(): bool
    {
        return $this->getStatus() === self::STATUS_REACHABLE;
    }

    public function canBeWoken(): bool
    {
        return (!empty($this->getMacAddress()) && !empty($this->getWakeDestination()) && !$this->isReachable());
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastSeen(): ?\DateTimeInterface
    {
        return $this->lastSeen;
    }

    public function setLastSeen(?\DateTimeInterface $lastSeen): self
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * Get the value of macAddress
     */
    public function getMacAddress(): string
    {
        return strval($this->macAddress);
    }

    /**
     * Set the value of macAddress
     *
     * @return  self
     */
    public function setMacAddress($macAddress)
    {
        $this->macAddress = $macAddress;

        return $this;
    }

    /**
     * Get the value of wakeDestination
     */
    public function getWakeDestination(): string
    {
        return strval($this->wakeDestination);
    }

    /**
     * Set the value of wakeDestination
     *
     * @return  self
     */
    public function setWakeDestination($wakeDestination)
    {
        $this->wakeDestination = $wakeDestination;

        return $this;
    }

    public function getShowOnDashboard(): bool
    {
        return $this->showOnDashboard;
    }

    public function setShowOnDashboard(bool $showOnDashboard): self
    {
        $this->showOnDashboard = $showOnDashboard;

        return $this;
    }
}