<?php

declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MoneyTransferRepository;

#[ORM\Entity(repositoryClass: MoneyTransferRepository::class)]
class MoneyTransfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: MoneyNode::class, inversedBy: 'outgoingTransfers')]
    #[ORM\JoinColumn(nullable: false)]
    private MoneyNode $sourceNode;

    #[ORM\ManyToOne(targetEntity: MoneyNode::class, inversedBy: 'incomingTransfers')]
    #[ORM\JoinColumn(nullable: false)]
    private MoneyNode $targetNode;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $operationDate;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $amount;

    #[ORM\Column(type: 'float', nullable: false)]
    private float $exchangeRate = 1.0;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'moneyTransfers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->operationDate = new \DateTime('today');
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getSourceNode(): MoneyNode
    {
        return $this->sourceNode;
    }

    public function setSourceNode(MoneyNode $sourceNode): self
    {
        $this->sourceNode = $sourceNode;
        return $this;
    }

    public function getTargetNode(): MoneyNode
    {
        return $this->targetNode;
    }

    public function setTargetNode(MoneyNode $targetNode): self
    {
        $this->targetNode = $targetNode;
        return $this;
    }

    public function getOperationDate(): \DateTimeInterface
    {
        return $this->operationDate;
    }

    public function getOperationDateString(): string
    {
        return $this->getOperationDate()->format('d.m.Y');
    }

    public function setOperationDate(\DateTimeInterface $operationDate): self
    {
        $this->operationDate = $operationDate;
        return $this;
    }

    public function setOperationDateString(string $operationDate): self
    {
        $date = new \DateTime($operationDate);
        $date->setTime(0, 0, 0, 0);
        return $this->setOperationDate($date);
    }

    public function getAmount(): float
    {
        return (floatval($this->amount) / 100.0);
    }

    public function setAmount(float $amount): self
    {
        if ($amount < 0.01) {
            throw new InvalidArgumentException('Amount can\'t be less than 0.01');
        }
        $this->amount = intval($amount * 100.0);
        return $this;
    }

    public function getExchangeRate(): float
    {
        if ($this->getSourceNode()->getCurrency() && $this->getTargetNode()->getCurrency()) {
            if (
                $this->getSourceNode()->getCurrency()
                === $this->getTargetNode()->getCurrency()
            ) {
                return 1.0;
            }
        }
        return $this->exchangeRate;
    }

    public function setExchangeRate(float $exchangeRate): self
    {
        if ($exchangeRate < 0) {
            throw new InvalidArgumentException('Exchange rate must be higher than 0');
        }
        $this->exchangeRate = $exchangeRate;
        return $this;
    }

    public function getExchangedAmount(): float
    {
        return round(
            num: floatval($this->getExchangeRate() * $this->getAmount()),
            precision: 2,
        );
    }

    public function getComment(): string
    {
        return strval($this->comment);
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
