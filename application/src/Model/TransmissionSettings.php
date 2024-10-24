<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\SimpleSettingsService;

final class TransmissionSettings
{
    public const BOTTOM_SPEED = 5;  // minimum - transmission ignores smaller values

    public const ADAPT_TYPE_UP_ONLY = 'UP_ONLY';

    public const MAX_AGGRESSION = 20;
    private const MIN_AGGRESSION = 2;

    public const TARGET_SPEED_FRAME_DAY = 'day';
    public const TARGET_SPEED_FRAME_FULL = 'full';

    private const HOST = 'TRANSMISSION_HOST';
    private const USER = 'TRANSMISSION_USER';
    private const PASSWORD = 'TRANSMISSION_PASSWORD';
    private const TARGET_SPEED = 'TRANSMISSION_TARGET_SPEED';
    private const TARGET_SPEED_MAX = 'TRANSMISSION_TARGET_SPEED_MAX';
    private const TARGET_SPEED_FRAME = 'TRANSMISSION_TARGET_SPEED_FRAME';
    private const TARGET_SPEED_BUMPING = 'TRANSMISSION_TARGET_SPEED_BUMPING';
    private const AGGRESSION = 'TRANSMISSION_AGGRESSION';
    private const AGGRESSION_ADAPT = 'TRANSMISSION_AGGRESSION_ADAPT';
    private const IS_ACTIVE = 'TRANSMISSION_THROTTLE_ACTIVE';

    private string $host;

    private string $user;

    private string $password;

    private string $targetSpeed;

    private int $targetSpeedMax;

    private string $targetFrame;

    private string $algorithmAggression;

    private string $aggressionAdapt;

    private string $allowSpeedBump;

    private string $isActive;

    public function selfConfigure(SimpleSettingsService $simpleSettingsService): void
    {
        $settingsArray = $simpleSettingsService->getSettings(
            keys: [
                self::IS_ACTIVE,
                self::HOST,
                self::USER,
                self::PASSWORD,
                self::TARGET_SPEED,
                self::TARGET_SPEED_MAX,
                self::TARGET_SPEED_FRAME,
                self::TARGET_SPEED_BUMPING,
                self::AGGRESSION,
                self::AGGRESSION_ADAPT,
            ],
            user: null,
        );
        $this->setIsActive(strval($settingsArray[self::IS_ACTIVE]));
        $this->setHost(strval($settingsArray[self::HOST]));
        $this->setUser(strval($settingsArray[self::USER]));
        $this->setPassword(strval($settingsArray[self::PASSWORD]));
        $this->setTargetSpeed(strval($settingsArray[self::TARGET_SPEED]));
        $this->setTargetSpeedMax(topSpeed: intval($settingsArray[self::TARGET_SPEED_MAX]), allowSelfConfigure: true);
        $this->setTargetFrame(strval($settingsArray[self::TARGET_SPEED_FRAME]));
        $this->setAllowSpeedBump(strval($settingsArray[self::TARGET_SPEED_BUMPING]));
        $this->setAlgorithmAggression(strval($settingsArray[self::AGGRESSION]));
        $this->setAggressionAdapt(strval($settingsArray[self::AGGRESSION_ADAPT]));
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService): void
    {
        $simpleSettingsService->saveSettings(
            settings: [
                self::IS_ACTIVE => $this->getIsActive(),
                self::HOST => $this->getHost(),
                self::USER => $this->getUser(),
                self::PASSWORD => $this->getPassword(),
                self::TARGET_SPEED => $this->getTargetSpeed(),
                self::TARGET_SPEED_MAX => strval($this->getTargetSpeedMax()),
                self::TARGET_SPEED_FRAME => $this->getTargetFrame(),
                self::TARGET_SPEED_BUMPING => $this->getAllowSpeedBump(),
                self::AGGRESSION => $this->getAlgorithmAggression(),
                self::AGGRESSION_ADAPT => $this->getAggressionAdapt(),
            ],
            user: null,
        );
    }

    public function getProposedThrottleSpeed(int|float $speedLeft): int
    {
        $speedLeftkB = $speedLeft / 1024;
        $targetSpeed = intval($this->getTargetSpeed());
        $aggression = intval($this->getAlgorithmAggression());
        $topSpeed = $this->getTargetSpeedMax();
        $speed = (($speedLeftkB - $targetSpeed) * $aggression) + $targetSpeed;
        $speed = intval($speed);
        if ($speed < self::BOTTOM_SPEED) {
            $speed = self::BOTTOM_SPEED;
        }
        if ($speed > $topSpeed) {
            $speed = $topSpeed;
        }
        return $speed;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getTargetSpeed(): string
    {
        return $this->targetSpeed;
    }

    public function setTargetSpeed(string $targetSpeed): self
    {
        $this->targetSpeed = $targetSpeed;
        return $this;
    }

    public function getTargetSpeedMax(): int
    {
        return $this->targetSpeedMax;
    }

    public function setTargetSpeedMax(int $topSpeed, bool $allowSelfConfigure = false): self
    {
        if (true === $allowSelfConfigure && $topSpeed < 1) {
            $topSpeed = 1024; //8 Mbit / 1 MByte default value
        }
        $this->targetSpeedMax = $topSpeed;
        return $this;
    }

    public function getAlgorithmAggression(): string
    {
        return $this->algorithmAggression;
    }

    public function setAlgorithmAggression(string $algorithmAggression): self
    {
        $int = intval($algorithmAggression);
        if ($int < self::MIN_AGGRESSION) {
            $int = self::MIN_AGGRESSION;
        }
        if ($int > self::MAX_AGGRESSION) {
            $int = self::MAX_AGGRESSION;
        }
        $this->algorithmAggression = strval($int);
        return $this;
    }

    public function getIsActive(): string
    {
        return $this->isActive;
    }

    public function setIsActive(string $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getAggressionAdapt(): string
    {
        return $this->aggressionAdapt;
    }

    public function setAggressionAdapt(string $aggressionAdapt): self
    {
        $this->aggressionAdapt = $aggressionAdapt;
        return $this;
    }

    public function getAllowSpeedBump(): string
    {
        return $this->allowSpeedBump;
    }

    public function setAllowSpeedBump(string $allowSpeedBump): self
    {
        $this->allowSpeedBump = $allowSpeedBump;
        return $this;
    }

    public function getTargetFrame(): string
    {
        return strval($this->targetFrame);
    }

    public function setTargetFrame(string $targetFrame): self
    {
        $this->targetFrame = $targetFrame;
        return $this;
    }
}
