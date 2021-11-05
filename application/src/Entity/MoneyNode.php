<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MoneyNodeRepository;

#[ORM\Entity(repositoryClass: MoneyNodeRepository::class)]
class MoneyNode
{
    public const NODE_TYPE_BANK_ACCOUNT = 0;
    public const NODE_TYPE_INCOME_SOURCE = 1;
    public const NODE_TYPE_OUTCOME = 2;
    public const NODE_TYPE_CASH_STASH = 3;
    public const NODE_TYPE_SERVICE = 4;
    public const NODE_TYPE_BLACK_HOLE = 5;
    public const NODE_TYPE_TAX_ACCOUNT = 6;
    public const NODE_TYPE_OTHER = 7;

    public const NODE_TYPES = [
        self::NODE_TYPE_BANK_ACCOUNT,
        self::NODE_TYPE_INCOME_SOURCE,
        self::NODE_TYPE_OUTCOME,
        self::NODE_TYPE_CASH_STASH,
        self::NODE_TYPE_SERVICE,
        self::NODE_TYPE_BLACK_HOLE,
        self::NODE_TYPE_TAX_ACCOUNT,
        self::NODE_TYPE_OTHER,
    ];

    public const NODE_TYPE_CODES = [
        self::NODE_TYPE_BANK_ACCOUNT => 'bank_account',
        self::NODE_TYPE_INCOME_SOURCE => 'income_source',
        self::NODE_TYPE_OUTCOME => 'outcome',
        self::NODE_TYPE_CASH_STASH => 'cash_stash',
        self::NODE_TYPE_SERVICE => 'service',
        self::NODE_TYPE_BLACK_HOLE => 'black_hole',
        self::NODE_TYPE_TAX_ACCOUNT => 'tax_account',
        self::NODE_TYPE_OTHER => 'other',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'moneyNodes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'smallint', nullable: false)]
    private int $nodeType;

    #[ORM\Column(type: 'smallint', nullable: false, options: ['default' => 0])]
    private int $nodeGroup = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $notes;

    #[ORM\OneToMany(mappedBy: 'targetNode', targetEntity: MoneyTransfer::class)]
    private $incomingTransfers;

    #[ORM\OneToMany(mappedBy: 'sourceNode', targetEntity: MoneyTransfer::class)]
    private $outgoingTransfers;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->incomingTransfers = new ArrayCollection();
        $this->outgoingTransfers = new ArrayCollection();
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

    public function getNodeType(): int
    {
        return $this->nodeType;
    }

    public function setNodeType(int $nodeType): self
    {
        if (!in_array(needle: $nodeType, haystack: self::NODE_TYPES)) {
            throw new InvalidArgumentException('Unknown Money Node Type: ' . $nodeType);
        }
        $this->nodeType = $nodeType;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Collection|MoneyTransfer[]
     */
    public function getIncomingTransfers(): Collection
    {
        return $this->incomingTransfers;
    }

    /**
     * @return Collection|MoneyTransfer[]
     */
    public function getOutgoingTransfers(): Collection
    {
        return $this->outgoingTransfers;
    }

    public function isEdgeType(): bool
    {
        return in_array(
            needle: $this->getNodeType(),
            haystack: [
                self::NODE_TYPE_INCOME_SOURCE,
                self::NODE_TYPE_BLACK_HOLE,
            ]
        );
    }

    public function getBalance(bool $force = false): float|string
    {
        if (false === $force && $this->isEdgeType()) {
            return '---';
        }
        $balance = 0.0;
        /**
         * @var MoneyTransfer
         */
        foreach ($this->getIncomingTransfers() as $incomingTransfer) {
            $balance = $balance + $incomingTransfer->getExchangedAmount();
            $balance = round(num: $balance, precision: 2);
        }
        /**
         * @var MoneyTransfer
         */
        foreach ($this->getOutgoingTransfers() as $outgoingTransfer) {
            $balance = $balance - $outgoingTransfer->getAmount();
            $balance = round(num: $balance, precision: 2);
        }
        return $balance;
    }

    public function getTypeCode(): string
    {
        return self::NODE_TYPE_CODES[$this->getNodeType()];
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function canBeDeleted(): bool
    {
        if ($this->getIncomingTransfers()->count() > 0) {
            return false;
        }
        if ($this->getOutgoingTransfers()->count() > 0) {
            return false;
        }
        return true;
    }

    public function getNodeGroup(): int
    {
        return $this->nodeGroup;
    }

    public function setNodeGroup(int $nodeGroup): self
    {
        $this->nodeGroup = $nodeGroup;
        return $this;
    }
}
