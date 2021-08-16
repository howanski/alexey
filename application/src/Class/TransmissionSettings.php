<?php

namespace App\Class;

use App\Service\SimpleSettingsService;

class TransmissionSettings
{
    private const HOST = 'TRANSMISSION_HOST';
    private const USER = 'TRANSMISSION_USER';
    private const PASSWORD = 'TRANSMISSION_PASSWORD';
    private const TARGET_SPEED = 'TRANSMISSION_TARGET_SPEED';
    private const AGGRESSION = 'TRANSMISSION_AGGRESSION';
    private const IS_ACTIVE = 'TRANSMISSION_THROTTLE_ACTIVE';

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $targetSpeed;

    /**
     * @var string
     */
    private $algorithmAggression;

    /**
     * @var string
     */
    private $isActive;

    public function selfConfigure(SimpleSettingsService $simpleSettingsService)
    {
        $settingsArray = $simpleSettingsService->getSettings([
            self::IS_ACTIVE,
            self::HOST,
            self::USER,
            self::PASSWORD,
            self::TARGET_SPEED,
            self::AGGRESSION,
        ]);
        $this->setIsActive(strval($settingsArray[self::IS_ACTIVE]));
        $this->setHost(strval($settingsArray[self::HOST]));
        $this->setUser(strval($settingsArray[self::USER]));
        $this->setPassword(strval($settingsArray[self::PASSWORD]));
        $this->setTargetSpeed(strval($settingsArray[self::TARGET_SPEED]));
        $this->setAlgorithmAggression(strval($settingsArray[self::AGGRESSION]));
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService)
    {
        $simpleSettingsService->saveSettings([
            self::IS_ACTIVE => $this->getIsActive(),
            self::HOST => $this->getHost(),
            self::USER => $this->getUser(),
            self::PASSWORD => $this->getPassword(),
            self::TARGET_SPEED => $this->getTargetSpeed(),
            self::AGGRESSION => $this->getAlgorithmAggression(),
        ]);
    }

    /**
     * @return  string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param  string  $host
     *
     * @return  self
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return  string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  string  $user
     *
     * @return  self
     */
    public function setUser(string $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return  string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param  string  $password
     *
     * @return  self
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return  string
     */
    public function getTargetSpeed()
    {
        return $this->targetSpeed;
    }

    /**
     * @param  string  $targetSpeed
     *
     * @return  self
     */
    public function setTargetSpeed(string $targetSpeed)
    {
        $this->targetSpeed = $targetSpeed;

        return $this;
    }

    /**
     * @return  string
     */
    public function getAlgorithmAggression()
    {
        return $this->algorithmAggression;
    }

    /**
     * @param  string  $algorithmAggression
     *
     * @return  self
     */
    public function setAlgorithmAggression(string $algorithmAggression)
    {
        $this->algorithmAggression = $algorithmAggression;

        return $this;
    }

    /**
     * @return  string
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param  string  $isActive
     *
     * @return  self
     */
    public function setIsActive(string $isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }
}
