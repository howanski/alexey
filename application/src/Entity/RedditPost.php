<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RedditPostRepository;
use Carbon\Carbon;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RedditPostRepository::class)]
#[UniqueEntity(fields: ['uri', 'channel'])]
class RedditPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $uri;

    #[ORM\Column(type: 'boolean')]
    private $seen = false;

    #[ORM\Column(type: 'text')]
    private $thumbnail = '';

    #[ORM\ManyToOne(targetEntity: RedditChannel::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private $channel;

    #[ORM\Column(type: 'string', length: 255)]
    private $title = '';

    #[ORM\Column(type: 'datetime')]
    private $published;

    #[ORM\Column(type: 'datetime')]
    private $touched;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $user = '';

    public function __construct()
    {
        $this->published = new DateTime('now');
        $this->touched = new DateTime('now');
    }

    public function getId(): int
    {
        return intval($this->id);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getSeen(): bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    public function getChannel(): RedditChannel
    {
        return $this->channel;
    }

    public function setChannel(RedditChannel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getPublished(): DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getTouched(): \DateTimeInterface
    {
        return $this->touched;
    }

    public function setTouched(\DateTimeInterface $touched): self
    {
        $this->touched = $touched;

        return $this;
    }

    public function publishTimereadable($locale)
    {
        $carbon = new Carbon($this->published);
        $carbon->setLocale($locale);
        return $carbon->diffForHumans();
    }

    public function getUriOld(): string
    {
        $uri = $this->getUri();
        $new = 'https://www.reddit.com/';
        $old = 'https://old.reddit.com/';
        return str_replace(search: $new, replace: $old, subject: $uri);
    }

    public function getUriUserOld(): string
    {
        $user = $this->getUser();
        $user = str_replace(search: '/u/', replace: '', subject: $user);
        return 'https://old.reddit.com/user/' . $user . '/submitted/?sort=top';
    }

    public function getUser(): string
    {
        return strval($this->user);
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }
}
