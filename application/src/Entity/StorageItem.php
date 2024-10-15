<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StorageItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

#[ORM\Entity(repositoryClass: StorageItemRepository::class)]
class StorageItem
{
    public const UNIT_QUANTITY = 'QUANTITY';

    private const VALID_UNITS = [
        self::UNIT_QUANTITY,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'storageItem', targetEntity: StorageItemStack::class, orphanRemoval: true)]
    private Collection $stacks;

    #[ORM\Column(type: 'string', length: 20)]
    private string $unitOfMeasure;

    #[ORM\OneToMany(mappedBy: 'storageItem', targetEntity: StorageItemAttribute::class, orphanRemoval: true)]
    private Collection $attributes;

    public function __construct()
    {
        $this->stacks = new ArrayCollection();
        $this->attributes = new ArrayCollection();
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

    public function getStacks(): Collection
    {
        return $this->stacks;
    }

    public function addStack(StorageItemStack $stack): self
    {
        if (!$this->stacks->contains($stack)) {
            $this->stacks[] = $stack;
            $stack->setStorageItem($this);
        }

        return $this;
    }

    public function removeStack(StorageItemStack $stack): self
    {
        $this->stacks->removeElement($stack);

        return $this;
    }

    public function getUnitOfMeasure(): string
    {
        return $this->unitOfMeasure;
    }

    public function setUnitOfMeasure(string $unitOfMeasure): self
    {
        if (!in_array(needle: $unitOfMeasure, haystack: self::VALID_UNITS)) {
            throw new LogicException(message: 'Unit Of Measure ' . $unitOfMeasure . ' not applicable to StorageItem');
        }

        $this->unitOfMeasure = $unitOfMeasure;

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(StorageItemAttribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setStorageItem($this);
        }

        return $this;
    }
}
