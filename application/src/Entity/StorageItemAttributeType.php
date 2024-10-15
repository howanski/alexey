<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

#[ORM\Entity()]
class StorageItemAttributeType
{
    public const TYPE_INTEGER = 'INT';

    private const VALID_TYPES = [
        self::TYPE_INTEGER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    #[ORM\OneToMany(mappedBy: 'attributeType', targetEntity: StorageItemAttribute::class)]
    private Collection $attributes;

    public function __construct()
    {
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array(needle: $type, haystack: self::VALID_TYPES)) {
            throw new LogicException(
                message: 'Type ' . $type . ' not applicable to StorageItemAttributeType'
            );
        }

        $this->type = $type;

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }
}
