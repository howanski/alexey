<?php

declare(strict_types=1);

namespace App\Class;

final class SideMenuItem
{
    public function __construct(
        private string $name,
        private string $destination,
        private string $icon,
        private bool $isActive,
        private array $children,
    ) {
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
