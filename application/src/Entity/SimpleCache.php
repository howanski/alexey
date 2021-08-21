<?php

namespace App\Entity;

use App\Repository\SimpleCacheRepository;
use Doctrine\ORM\Mapping as ORM;

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
}
