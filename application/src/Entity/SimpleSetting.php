<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SimpleSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimpleSettingRepository::class)]
class SimpleSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $settingKey;

    #[ORM\Column(type: 'string', length: 255)]
    private $settingValue;

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
}
