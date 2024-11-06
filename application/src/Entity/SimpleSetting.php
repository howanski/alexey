<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SimpleSettingRepository;

#[ORM\Entity(repositoryClass: SimpleSettingRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class SimpleSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $settingKey;

    #[ORM\Column(type: 'string', length: 255)]
    private string $settingValue;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'settings')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSettingKey(): string
    {
        return $this->settingKey;
    }

    public function setSettingKey(string $settingKey): self
    {
        $this->settingKey = $settingKey;
        return $this;
    }

    public function getSettingValue(): string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $settingValue): self
    {
        $this->settingValue = $settingValue;
        return $this;
    }

    public function getUser(): User|null
    {
        return $this->user;
    }

    public function setUser($user): self
    {
        $this->user = $user;
        return $this;
    }
}
