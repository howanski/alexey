<?php

declare(strict_types=1);

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NetworkMachineRepository;

#[ORM\Entity(repositoryClass: NetworkMachineRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class NetworkMachine
{
    public const STATUS_UNKNOWN = 0;
    public const STATUS_UNREACHABLE = 1;
    public const STATUS_REACHABLE = 2;

    private const STATUSES_READABLE = [
        self::STATUS_UNKNOWN => 'unknown',
        self::STATUS_UNREACHABLE => 'unreachable',
        self::STATUS_REACHABLE => 'visible',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $uri;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 23, nullable: true)]
    private ?string $macAddress;

    #[ORM\Column(type: 'string', length: 23, nullable: true)]
    private ?string $wakeDestination;

    #[ORM\Column(type: 'smallint')]
    private int $status;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastSeen;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showOnDashboard = false;

    public function __construct()
    {
        $this->status = self::STATUS_UNKNOWN;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStatus(): int
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
        return (strlen($this->getMacAddress()) > 0
            && strlen($this->getWakeDestination()) > 0
            && false === $this->isReachable());
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getLastSeen(): \DateTimeInterface|null
    {
        return $this->lastSeen;
    }

    public function setLastSeen(\DateTimeInterface $lastSeen): self
    {
        $this->lastSeen = $lastSeen;
        return $this;
    }

    public function getMacAddress(): string
    {
        return strval($this->macAddress);
    }

    public function setMacAddress($macAddress): self
    {
        $this->macAddress = $macAddress;
        return $this;
    }

    public function getWakeDestination(): string
    {
        return strval($this->wakeDestination);
    }

    public function setWakeDestination($wakeDestination): self
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

    public function getLastSeenReadable(string $locale): string
    {
        $conventionalTime = $this->getLastSeen();
        $carbonised = new Carbon($conventionalTime);
        $carbonised->setLocale($locale);
        return $carbonised->diffForHumans();
    }
}
