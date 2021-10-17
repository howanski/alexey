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
    private float $exchangeRate;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    public function __construct(
        MoneyNode $sourceNode,
        MoneyNode $targetNode,
        float $amount,
        float $exchangeRate = 1.0,
        \DateTimeInterface $operationDate = null,
    ) {
        if (is_null($operationDate)) {
            $operationDate = new \DateTime('today');
        }
        $this->setSourceNode($sourceNode);
        $this->setTargetNode($targetNode);
        $this->setAmount($amount);
        $this->setExchangeRate($exchangeRate);
        $this->setOperationDate($operationDate);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceNode(): MoneyNode
    {
        return $this->sourceNode;
    }

    private function setSourceNode(MoneyNode $sourceNode): self
    {
        $this->sourceNode = $sourceNode;
        return $this;
    }

    public function getTargetNode(): MoneyNode
    {
        return $this->targetNode;
    }

    private function setTargetNode(MoneyNode $targetNode): self
    {
        $this->targetNode = $targetNode;
        return $this;
    }

    public function getOperationDate(): \DateTimeInterface
    {
        return $this->operationDate;
    }

    private function setOperationDate(\DateTimeInterface $operationDate): self
    {
        $this->operationDate = $operationDate;
        return $this;
    }

    public function getAmount(): float
    {
        return ($this->amount / 100.0);
    }

    private function setAmount(float $amount): self
    {
        if ($amount < 0.01) {
            throw new InvalidArgumentException('Amount can\'t be less than 0.01');
        }
        $this->amount = intval($amount * 100.0);
        return $this;
    }

    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    private function setExchangeRate(float $exchangeRate): self
    {
        if ($exchangeRate < 0) {
            throw new InvalidArgumentException('Exchange rate must be higher than 0');
        }
        $this->exchangeRate = $exchangeRate;
        return $this;
    }

    public function getExchangedAmount(): float
    {
        return intval($this->exchangeRate * $this->amount) * 100.0;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
