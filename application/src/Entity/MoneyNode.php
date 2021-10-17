<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
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

    private const NODE_TYPES = [
        self::NODE_TYPE_BANK_ACCOUNT,
        self::NODE_TYPE_INCOME_SOURCE,
        self::NODE_TYPE_OUTCOME,
        self::NODE_TYPE_CASH_STASH,
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

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
