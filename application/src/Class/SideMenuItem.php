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

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var bool
     */
    private $isHeading;

    /**
     * @var bool
     */
    private $isDivider;

    public function __construct(
        string $name = '',
        string $destination = '/',
        string $icon = 'fa-cog',
        bool $isActive = false,
        bool $isHeading = false,
        array $children = [],
    ) {
        $this->setName($name);
        $this->setDestination($destination);
        $this->setIcon($icon);
        $this->setIsActive($isActive);
        $this->setIsHeading($isHeading);
        $this->setChildren($children);
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

    public function haveChildren()
    {
        return !empty($this->getChildren());
    }

    /**
     * Get the value of children
     *
     * @return  []SideMenuItem
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Set the value of children
     *
     * @param  array  $children
     *
     * @return  self
     */
    public function setChildren(array $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get the value of isHeading
     *
     * @return  bool
     */
    public function getIsHeading()
    {
        return $this->isHeading;
    }

    /**
     * Set the value of isHeading
     *
     * @param  bool  $isHeading
     *
     * @return  self
     */
    public function setIsHeading(bool $isHeading)
    {
        $this->isHeading = $isHeading;

        return $this;
    }

    /**
     * Get the value of isDivider
     *
     * @return  bool
     */
    public function getIsDivider()
    {
        return $this->isDivider;
    }

    /**
     * Set the value of isDivider
     *
     * @param  bool  $isDivider
     *
     * @return  self
     */
    public function setIsDivider(bool $isDivider)
    {
        $this->isDivider = $isDivider;

        return $this;
    }
}
