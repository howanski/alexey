<?php

namespace App\Class;

class NetworkUsageProviderSettings
{
    /**
     * @var string
     */
    private $providerType;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $password;

    /**
     * @return  string
     */
    public function getProviderType()
    {
        return $this->providerType;
    }

    /**
     * @param  string  $providerType
     *
     * @return  self
     */
    public function setProviderType(string $providerType)
    {
        $this->providerType = $providerType;

        return $this;
    }

    /**
     * @return  string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param  string  $address
     *
     * @return  self
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

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
}
