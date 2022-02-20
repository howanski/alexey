<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\MoneyNode;
use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[UniqueEntity(fields: ['user', 'code'])]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 3, nullable: false)]
    private string $code;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    private bool $isMain;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'currency', targetEntity: MoneyNode::class)]
    private $moneyNodes;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->isMain = false;
        $this->moneyNodes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return strtoupper($this->code);
    }

    public function setCode(string $code): self
    {
        $this->code = strtoupper($code);
        return $this;
    }

    public function getIsMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Collection|MoneyNode[]
     */
    public function getMoneyNodes(): Collection
    {
        return $this->moneyNodes;
    }
}
