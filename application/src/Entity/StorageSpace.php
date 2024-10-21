<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StorageSpaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: StorageSpaceRepository::class)]
#[UniqueEntity(fields: ['name', 'user'])]
class StorageSpace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'storageSpaces')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'storageSpace', targetEntity: StorageItemStack::class, orphanRemoval: true)]
    private Collection $storageItemStacks;

    public function __construct()
    {
        $this->storageItemStacks = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStorageItemStacks(): Collection
    {
        return $this->storageItemStacks;
    }

    public function hasStacks(): bool
    {
        return $this->getStorageItemStacks()->count() > 0;
    }
}
