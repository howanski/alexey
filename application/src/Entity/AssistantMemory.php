<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AssistantMemoryRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssistantMemoryRepository::class)]
class AssistantMemory
{
    public const STATUS_CREATED_BY_ASSISTANT = 1;
    public const STATUS_EDITED_BY_ASSISTANT = 2;
    public const STATUS_RESOLVED_BY_USER = 3;
    public const STATUS_DELETED_BY_ASSISTANT = 4;

    public const TITLE_WORD_LIMIT = 4;

    private const STATUSES = [
        self::STATUS_CREATED_BY_ASSISTANT,
        self::STATUS_EDITED_BY_ASSISTANT,
        self::STATUS_RESOLVED_BY_USER,
        self::STATUS_DELETED_BY_ASSISTANT,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: AssistantRecurringMessage::class)]
    #[ORM\JoinColumn(nullable: false)]
    private AssistantRecurringMessage $assistant;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $userContent = '';

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $assistantContent = '';

    #[ORM\Column(type: Types::SMALLINT)]
    private int $status = self::STATUS_CREATED_BY_ASSISTANT;

    #[ORM\Column(nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(length: 255)]
    private string $userTitle = '';

    #[ORM\Column(length: 255)]
    private string $assistantTitle = '';

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
    }

    public function acceptUserVersion(): void
    {
        $this->setAssistantContent($this->getUserContent());
        $this->setAssistantTitle($this->getUserTitle());
        $this->setStatus(self::STATUS_RESOLVED_BY_USER);
    }

    public function acceptAssistantVersion(): void
    {
        $this->setUserContent($this->getAssistantContent());
        $this->setUserTitle($this->getAssistantTitle());
        $this->setStatus(self::STATUS_RESOLVED_BY_USER);
    }

    public static function createNew(AssistantRecurringMessage $assistant, string $title, string $content): self
    {
        $memory = new self();
        $memory->setAssistant($assistant);
        $memory->setStatus(self::STATUS_CREATED_BY_ASSISTANT);
        $memory->setAssistantTitle($title);
        $memory->setAssistantContent($content);
        return $memory;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAssistant(): AssistantRecurringMessage
    {
        return $this->assistant;
    }

    public function setAssistant(AssistantRecurringMessage $assistant): static
    {
        $this->assistant = $assistant;
        return $this;
    }

    public function getUserContent(): string
    {
        return $this->userContent;
    }

    public function setUserContent(string $content): static
    {
        $this->userContent = $content;
        return $this;
    }

    public function getAssistantContent(): string
    {
        return $this->assistantContent;
    }

    public function getAssistantContentShort(int $charLimit = 10): string
    {
        $string = (string) $this->getAssistantContent();
        if ($charLimit < 4) {
            $charLimit = 4;
        }
        if (strlen($string) > $charLimit) {
            $string = substr($string, 0, $charLimit - 3) . '...';
        }
        return $string;
    }

    public function setAssistantContent(string $content): static
    {
        $this->assistantContent = $content;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function isMarkedForDeletion(): bool
    {
        return $this->getStatus() === self::STATUS_DELETED_BY_ASSISTANT;
    }

    public function isUserApproved(): bool
    {
        return $this->getStatus() === self::STATUS_RESOLVED_BY_USER;
    }

    public function isNewMemory(): bool
    {
        return $this->getStatus() === self::STATUS_CREATED_BY_ASSISTANT;
    }

    public function isEditedMemory(): bool
    {
        return $this->getStatus() === self::STATUS_EDITED_BY_ASSISTANT;
    }

    public function setStatus(int $status): static
    {
        if (in_array($status, self::STATUSES, true)) {
            $this->status = $status;
        }
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUserTitle(): string
    {
        return $this->userTitle;
    }

    public function setUserTitle(string $userTitle): static
    {
        $this->userTitle = $userTitle;

        return $this;
    }

    public function getAssistantTitle(): string
    {
        return $this->assistantTitle;
    }

    public function setAssistantTitle(string $assistantTitle): static
    {
        $this->assistantTitle = $assistantTitle;

        return $this;
    }
}
