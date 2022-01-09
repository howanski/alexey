<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RedditChannelGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RedditChannelGroupRepository::class)]
#[UniqueEntity(fields: ['user', 'name'])]
final class RedditChannelGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name = '';

    #[ORM\OneToMany(mappedBy: 'channelGroup', targetEntity: RedditChannel::class)]
    private $channels;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'integer')]
    private $orderNumber = 0;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
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

    /**
     * @return Collection|RedditChannel[]
     */
    public function getChannels(): Collection
    {
        return $this->channels;
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

    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(int $orderNumber): self
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getChannelCount(): int
    {
        return $this->getChannels()->count();
    }
}
