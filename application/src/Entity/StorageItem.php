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
    public const UNIT_BOX = 'BOX';
    public const UNIT_CENTIMETER = 'CENTIMETER';
    public const UNIT_GRAM = 'GRAM';
    public const UNIT_KILOGRAM = 'KILOGRAM';
    public const UNIT_METER = 'METER';
    public const UNIT_QUANTITY = 'QUANTITY';
    public const UNIT_VOLUME_LITER = 'LITER';
    public const UNIT_VOLUME_MILILITER = 'MILILITER';

    public const VALID_UNITS = [
        self::UNIT_QUANTITY,
        self::UNIT_BOX,
        self::UNIT_METER,
        self::UNIT_CENTIMETER,
        self::UNIT_GRAM,
        self::UNIT_KILOGRAM,
        self::UNIT_VOLUME_LITER,
        self::UNIT_VOLUME_MILILITER,
    ];

    public const VALID_UNITS_TRANS_CODES = [
        self::UNIT_BOX => 'box',
        self::UNIT_CENTIMETER => 'centimeter',
        self::UNIT_GRAM => 'gram',
        self::UNIT_KILOGRAM => 'kilogram',
        self::UNIT_METER => 'meter',
        self::UNIT_QUANTITY => 'quantity',
        self::UNIT_VOLUME_LITER => 'liter',
        self::UNIT_VOLUME_MILILITER => 'mililiter',
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

    #[ORM\Column(type: 'integer')]
    private int $minimalQuantity;

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

    public function getUnitOfMeasureTranslationCode(): string
    {
        return self::VALID_UNITS_TRANS_CODES[$this->getUnitOfMeasure()];
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

    public function getMinimalQuantity(): int
    {
        return $this->minimalQuantity;
    }

    public function setMinimalQuantity(int $minimalQuantity): self
    {
        $this->minimalQuantity = $minimalQuantity;

        return $this;
    }

    public function getQuantity(): int
    {
        $totalQuantity = 0;
        /** @var StorageItemStack $stack */
        foreach ($this->getStacks() as $stack) {
            $totalQuantity += $stack->getQuantity();
        }
        return $totalQuantity;
    }

    public function getStorageSpacesReadable(): string
    {
        $spaces = [];
        /** @var StorageItemStack $stack */
        foreach ($this->getStacks() as $stack) {
            $spaceName = $stack->getStorageSpace()->getName();
            $spaces[$spaceName] = $spaceName;
        }

        ksort($spaces);

        return implode(separator: ', ', array: array_keys(array: $spaces));
    }
}
