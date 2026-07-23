<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\AssistantMessageDTO;
use App\Repository\AssistantCallRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\AI\Platform\TokenUsage\TokenUsage;

#[ORM\Entity(repositoryClass: AssistantCallRepository::class)]
class AssistantCall
{
    public const TYPE_CHAT = 1;
    public const TYPE_RESEARCH = 2;
    public const TYPE_TRASH = 0;

    // TODO: Enums are cool, does this doctrine version support it?
    public const STATUS_DONE = 5;
    public const STATUS_DRAFT = 1;
    public const STATUS_ERROR = 4;
    public const STATUS_PROCESSING = 3;
    public const STATUS_READY_TO_PROCESS = 2;
    public const STATUS_TO_REDO = 7;
    public const STATUS_WAITING_FOR_CHILDREN = 6;

    private const STATUSES = [
        self::STATUS_DONE => 'STATUS_DONE',
        self::STATUS_DRAFT => 'STATUS_DRAFT',
        self::STATUS_ERROR => 'STATUS_ERROR',
        self::STATUS_PROCESSING => 'STATUS_PROCESSING',
        self::STATUS_READY_TO_PROCESS => 'STATUS_READY_TO_PROCESS',
        self::STATUS_TO_REDO => 'STATUS_TO_REDO',
        self::STATUS_WAITING_FOR_CHILDREN => 'STATUS_WAITING_FOR_CHILDREN',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $userQuery = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $assistantResponse = null;

    #[ORM\Column]
    private DateTime $lastStatusChange;

    #[ORM\Column(type: Types::ARRAY)]
    private array $metadata = [];

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $root = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $status = self::STATUS_DRAFT;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $type = self::TYPE_CHAT;

    #[ORM\ManyToOne(targetEntity: AssistantRecurringMessage::class)]
    private ?AssistantRecurringMessage $systemMessage = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $tools = [];

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->lastStatusChange = new DateTime('now');
    }

    public static function fromMessageDTO(User $user, AssistantMessageDTO $dto): self
    {
        $call = new self();
        $call->setUserQuery($dto->getMessage());
        $call->setUser($user);
        $call->setStatus(self::STATUS_READY_TO_PROCESS);
        $call->setTools($dto->getTools());
        return $call;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserQuery(): ?string
    {
        return $this->userQuery;
    }

    public function setUserQuery(string $userQuery): static
    {
        $this->userQuery = $userQuery;

        return $this;
    }

    public function getAssistantResponse(): ?string
    {
        return $this->assistantResponse;
    }

    public function setAssistantResponse(?string $assistantResponse): static
    {
        $this->assistantResponse = $assistantResponse;

        return $this;
    }

    public function getLastStatusChange(): ?DateTime
    {
        return $this->lastStatusChange;
    }

    public function setLastStatusChange(DateTime $lastStatusChange): static
    {
        $this->lastStatusChange = $lastStatusChange;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function getRootEntity(): self
    {
        if ($this->root instanceof static) {
            return $this->root->getRootEntity();
        }
        return $this;
    }

    public function setRoot(?self $root): static
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        if (!($this->status === $status) && array_key_exists($status, self::STATUSES)) {
            $this->status = $status;
            $this->lastStatusChange = new DateTime('now');
        }
        return $this;
    }

    public function getStatusEffective(): string
    {
        foreach ($this->getChildren() as $child) {
            // TODO: needs logic rework when research feature gets implemented
            return $child->getStatusEffective();
        }
        $status = $this->getStatus();
        return self::STATUSES[$status];
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

    public function getSystemMessage(): ?AssistantRecurringMessage
    {
        return $this->systemMessage;
    }

    public function setSystemMessage(?AssistantRecurringMessage $systemMessage): static
    {
        $this->systemMessage = $systemMessage;

        return $this;
    }

    public function getTools(): array
    {
        return $this->tools;
    }

    public function setTools(array $tools): static
    {
        $this->tools = $tools;

        return $this;
    }

    public function getShortName(int $charLimit = 10): string
    {
        $string = $this->getUserQuery();
        if ($charLimit < 4) {
            $charLimit = 4;
        }
        if (strlen($string) > $charLimit) {
            $string = substr($string, 0, $charLimit - 3) . '...';
        }
        return $string;
    }

    public function getTokenUsage(): array
    {
        if (($this->metadata['token_usage'] ?? null) instanceof TokenUsage) {
            return [
                'promptTokens' => $this->metadata['token_usage']->getPromptTokens(),
                'completionTokens' => $this->metadata['token_usage']->getCompletionTokens(),
            ];
        }
        return ['promptTokens' => null, 'completionTokens' => null];
    }

    public function getMessagesTimeline(): array
    {
        $timeLine = [];

        $timeLine[] = [
            'request' => $this->getUserQuery(),
            'response' => $this->getAssistantResponse(),
            'isProcessing' => !($this->getStatus() === self::STATUS_DONE),
            'id' => $this->getId(),
            'token_usage' => $this->getTokenUsage(),
            'responder' => strval($this->getSystemMessage()?->getName()),
        ];
        foreach ($this->getChildren() as $child) {
            // There should be max 1 child for chat but no limit for research
            // TODO: refine research type timeline logic
            $childMessageTimeline = $child->getMessagesTimeline();
            foreach ($childMessageTimeline as $childTimeLine) {
                $timeLine[] = $childTimeLine;
            }
        }
        return $timeLine;
    }

    public function getLastChild(): self
    {
        // TODO: rewrite logic when introducing research module - there will be more than one child
        // for streamlined chat it will work ok
        foreach ($this->getChildren() as $child) {
            return $child->getLastChild();
        }
        return $this;
    }
}
