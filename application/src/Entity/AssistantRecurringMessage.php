<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AssistantRecurringMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AssistantRecurringMessageRepository::class)]
#[UniqueEntity(fields: ['type', 'user', 'priority'])]
class AssistantRecurringMessage
{
    public const DEFAULT_PRIORITY = 0;
    public const TYPE_SYSTEM_MESSAGE = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $type = self::TYPE_SYSTEM_MESSAGE;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private int $priority = 0;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $model = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    // something unique for dropdown
    public function getDisplayName(): string
    {
        if ($this->isDefault()) {
            return '[👑][' . $this->getModel() . ']';
        }
        return '#' . strval($this->getId()) . ' ' . $this->getName() . ' [' . $this->getModel() . ']';
    }

    public function isDefault(): bool
    {
        return $this->priority === self::DEFAULT_PRIORITY;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = strval($name);

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = strval($model);

        return $this;
    }
}
