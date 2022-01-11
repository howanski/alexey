<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\SimpleSetting;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $username;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 2, options: ['default' => 'en'])]
    private string $locale = 'en';

    #[ORM\OneToMany(targetEntity: SimpleSetting::class, mappedBy: 'user', orphanRemoval: true)]
    private $settings;

    #[ORM\OneToMany(targetEntity: SimpleCache::class, mappedBy: 'user', orphanRemoval: true)]
    private $caches;

    #[ORM\OneToMany(targetEntity: MoneyNode::class, mappedBy: 'user', orphanRemoval: true)]
    private $moneyNodes;

    #[ORM\OneToMany(targetEntity: MoneyTransfer::class, mappedBy: 'user', orphanRemoval: true)]
    #[ORM\OrderBy(['operationDate' => 'DESC', 'comment' => 'ASC'])]
    private $moneyTransfers;

    #[ORM\Column(type: 'string', length: 254, options: ['default' => ''])]
    private string $email = '';

    #[ORM\Column(type: 'string', length: 15, options: ['default' => ''])]
    private string $otp = '';

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RedditChannel::class, orphanRemoval: true)]
    private $redditChannels;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
        $this->caches = new ArrayCollection();
        $this->moneyNodes = new ArrayCollection();
        $this->moneyTransfers = new ArrayCollection();
        $this->redditChannels = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): string|null
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function getCaches(): Collection
    {
        return $this->caches;
    }


    public function getMoneyTransfers(): Collection
    {
        return $this->moneyTransfers;
    }

    public function getMoneyNodes(): Collection
    {
        return $this->moneyNodes;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRedditChannels(): Collection
    {
        return $this->redditChannels;
    }

    public function getOtp(): string
    {
        return $this->otp;
    }

    public function setOtp(string $otp): self
    {
        $this->otp = $otp;
        return $this;
    }
}
