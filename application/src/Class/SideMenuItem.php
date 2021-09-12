<?php

declare(strict_types=1);

namespace App\Class;

final class SideMenuItem
{

    private string $icon;

    private bool $isActive;

    private string $name;

    private string $destination;

    private array $children;

    private bool $isHeading;

    private bool $isDivider;

    public function __construct(
        string $name = '',
        string $destination = '/',
        string $icon = 'fa-cog',
        bool $isActive = false,
        bool $isHeading = false,
        array $children = [],
        bool $isDivider = false,
    ) {
        $this->setName($name);
        $this->setDestination($destination);
        $this->setIcon($icon);
        $this->setIsActive($isActive);
        $this->setIsHeading($isHeading);
        $this->setChildren($children);
        $this->setIsDivider($isDivider);
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
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

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    public function haveChildren(): bool
    {
        return !empty($this->getChildren());
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;
        return $this;
    }

    public function getIsHeading(): bool
    {
        return $this->isHeading;
    }

    public function setIsHeading(bool $isHeading): self
    {
        $this->isHeading = $isHeading;

        return $this;
    }

    public function getIsDivider(): bool
    {
        return $this->isDivider;
    }

    public function setIsDivider(bool $isDivider): self
    {
        $this->isDivider = $isDivider;

        return $this;
    }
}
