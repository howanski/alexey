<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RedditChannelRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RedditChannelRepository::class)]
#[UniqueEntity(fields: ['user', 'name'])]
class RedditChannel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name = '';

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'redditChannels')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'datetime')]
    private $lastFetch;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: RedditPost::class, orphanRemoval: true)]
    private $posts;

    #[ORM\ManyToOne(targetEntity: RedditChannelGroup::class, inversedBy: 'channels')]
    private $channelGroup;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->lastFetch = new DateTime('last year');
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLastFetch(): DateTimeInterface
    {
        return $this->lastFetch;
    }

    public function setLastFetch(DateTimeInterface $lastFetch): self
    {
        $this->lastFetch = $lastFetch;
        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getChannelGroup()
    {
        return $this->channelGroup;
    }

    public function setChannelGroup($channelGroup): self
    {
        $this->channelGroup = $channelGroup;

        return $this;
    }
}
