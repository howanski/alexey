<?php

namespace App\Class;

class SideMenuItem
{
    /**
     * @var string
     */
    private $icon;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $destination;

    public function __construct(
        string $name = '',
        string $destination = '/',
        string $icon = 'fa-cog',
        bool $isActive = false
    ) {
        $this->setName($name);
        $this->setDestination($destination);
        $this->setIcon($icon);
        $this->setIsActive($isActive);
    }

    /**
     * Get the value of icon
     *
     * @return  string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the value of icon
     *
     * @param  string  $icon
     *
     * @return  self
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get the value of isActive
     *
     * @return  bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set the value of isActive
     *
     * @param  bool  $isActive
     *
     * @return  self
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of destination
     *
     * @return  string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set the value of destination
     *
     * @param  string  $destination
     *
     * @return  self
     */
    public function setDestination(string $destination)
    {
        $this->destination = $destination;

        return $this;
    }
}
