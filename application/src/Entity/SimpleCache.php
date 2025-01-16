<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SimpleCacheRepository;

#[ORM\Entity(repositoryClass: SimpleCacheRepository::class)]
class SimpleCache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $cacheKey;

    #[ORM\Column(type: 'json')]
    private array $cacheData = [];

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $validTo;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'caches')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(string $cacheKey): self
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }

    public function getCacheData(): array
    {
        return $this->cacheData;
    }

    public function setCacheData($cacheData): self
    {
        $this->cacheData = $cacheData;
        return $this;
    }

    public function getValidTo(): \DateTimeInterface
    {
        return $this->validTo;
    }

    public function setValidTo(\DateTimeInterface $validTo): self
    {
        $this->validTo = $validTo;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
