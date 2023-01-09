<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RedditBannedPosterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RedditBannedPosterRepository::class)]
class RedditBannedPoster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $username;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'redditBannedPosters')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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
