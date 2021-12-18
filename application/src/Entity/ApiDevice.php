<?php

namespace App\Entity;

use App\Repository\ApiDeviceRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ApiDeviceRepository::class)]
#[UniqueEntity(fields: ['secret'])]
class ApiDevice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 255)]
    private $secret;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'datetime')]
    private $lastRequest;

    #[ORM\Column(type: 'array')]
    private $permissions = [];

    public function getId(): int
    {
        return $this->id;
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

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
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

    public function getLastRequest(): DateTime
    {
        return $this->lastRequest;
    }

    public function setLastRequest(DateTime $lastRequest): self
    {
        $this->lastRequest = $lastRequest;
        return $this;
    }

    public function resetPermissions(): self
    {
        $this->permissions = [];
        return $this;
    }

    public function getPermissions(): array
    {
        return array_values($this->permissions);
    }

    public function havePermission(string $permission): bool
    {
        return in_array(needle: $permission, haystack: $this->permissions);
    }

    public function addPermission(string $permission): self
    {
        $perms = $this->getPermissions();
        $perms[] = $permission;
        $this->permissions = array_values(array_unique($perms));
        return $this;
    }

    public function removePermission(string $permission): self
    {
        $perms = $this->getPermissions();
        foreach ($perms as $key => $perm) {
            if ($perm === $permission) {
                unset($perms[$key]);
            }
        }
        $this->permissions = array_values($perms);
        return $this;
    }
}
