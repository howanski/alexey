<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class StorageItemStack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: StorageSpace::class, inversedBy: 'storageItemStacks')]
    #[ORM\JoinColumn(nullable: false)]
    private StorageSpace $storageSpace;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\ManyToOne(targetEntity: StorageItem::class, inversedBy: 'stacks')]
    #[ORM\JoinColumn(nullable: false)]
    private StorageItem $storageItem;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStorageSpace(): StorageSpace
    {
        return $this->storageSpace;
    }

    public function setStorageSpace(StorageSpace $storageSpace): self
    {
        $this->storageSpace = $storageSpace;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getStorageItem(): StorageItem
    {
        return $this->storageItem;
    }

    public function setStorageItem(StorageItem $storageItem): self
    {
        $this->storageItem = $storageItem;

        return $this;
    }
}
