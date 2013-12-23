<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM;
use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Entity\Credentials;

/**
 * Represents a Tokenized account
 *
 * Tokenized payments are usually related to a single merchant account via
 * its Credentials property.
 *
 * @ORM\Entity
 */
class TokenAccount extends AbstractAccount
{
    /**
     * @var string
     * 
     * @ORM\Column(name="identifier", type="string", nullable=true)
     */
    protected $identifier;

    /**
     * @var \Orkestra\Transactor\Entity\Credentials $credentials
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\Credentials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="credentials_id", referencedColumnName="id")
     * })
     */
    protected $credentials;

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param \Orkestra\Transactor\Entity\Credentials $credentials
     */
    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return \Orkestra\Transactor\Entity\Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
    
    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Token';
    }
}
