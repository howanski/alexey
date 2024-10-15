<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class StorageItemAttribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $value;

    #[ORM\ManyToOne(targetEntity: StorageItem::class, inversedBy: 'attributes')]
    #[ORM\JoinColumn(nullable: false)]
    private StorageItem $storageItem;

    #[ORM\ManyToOne(targetEntity: StorageItemAttributeType::class, inversedBy: 'attributes')]
    #[ORM\JoinColumn(nullable: false)]
    private StorageItemAttributeType $attributeType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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

    public function getAttributeType(): StorageItemAttributeType
    {
        return $this->attributeType;
    }

    public function setAttributeType(StorageItemAttributeType $attributeType): self
    {
        $this->attributeType = $attributeType;

        return $this;
    }
}
