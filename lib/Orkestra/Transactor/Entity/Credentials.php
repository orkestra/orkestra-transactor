<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM;
use Orkestra\Common\Entity\AbstractEntity;
use Orkestra\Transactor\TransactorInterface;

/**
 * Represents credentials used to authenticate with a transactor
 *
 * @ORM\Table(name="orkestra_credentials")
 * @ORM\Entity
 */
class Credentials extends AbstractEntity
{
    /**
     * @var array
     *
     * @ORM\Column(name="credentials", type="array")
     */
    protected $credentials = array();

    /**
     * @var string
     *
     * @ORM\Column(name="transactor", type="string")
     */
    protected $transactor;

    /**
     * Gets a property from the stored credentials
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->getCredential($property);
    }

    /**
     * Sets a property in the stored credentials
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value)
    {
        $this->credentials[$property] = $value;
    }

    /**
     * Set Credential
     *
     * @param string $key   The key of which credential to set
     * @param mixed  $value
     */
    public function setCredential($key, $value)
    {
        $this->credentials[$key] = $value;
    }

    /**
     * Get Credential
     *
     * @param  string $key The key of which credential to get
     * @return mixed
     */
    public function getCredential($key)
    {
        return empty($this->credentials[$key]) ? null : $this->credentials[$key];
    }

    /**
     * Gets the credentials
     *
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Sets the credentials
     *
     * @param $value
     */
    public function setCredentials($value)
    {
        $this->credentials = $value;
    }

    /**
     * Sets the transactor
     *
     * @param \Orkestra\Transactor\TransactorInterface|string $transactor
     */
    public function setTransactor($transactor)
    {
        $this->transactor = is_object($transactor) ? $transactor->getType() : $transactor;
    }

    /**
     * Gets the transactor
     *
     * @return string
     */
    public function getTransactor()
    {
        return $this->transactor;
    }
}
