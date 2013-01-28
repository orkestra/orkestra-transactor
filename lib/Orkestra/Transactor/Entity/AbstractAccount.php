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
use Doctrine\Common\Collections\ArrayCollection;
use Orkestra\Common\Entity\AbstractEntity;

/**
 * Base class for any Account entity
 *
 * @ORM\Table(name="orkestra_accounts")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "BankAccount"       = "Orkestra\Transactor\Entity\Account\BankAccount",
 *   "CardAccount"       = "Orkestra\Transactor\Entity\Account\CardAccount",
 *   "PointsAccount"     = "Orkestra\Transactor\Entity\Account\PointsAccount",
 *   "SimpleAccount"     = "Orkestra\Transactor\Entity\Account\SimpleAccount",
 *   "SwipedCardAccount" = "Orkestra\Transactor\Entity\Account\SwipedCardAccount"
 * })
 */
abstract class AbstractAccount extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="account_number", type="string")
     */
    protected $accountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string")
     */
    protected $ipAddress = '';

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string")
     */
    protected $alias = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string")
     */
    protected $address = '';

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string")
     */
    protected $city = '';

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=3)
     */
    protected $region = '';

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=3)
     */
    protected $country = '';

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string")
     */
    protected $postalCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string")
     */
    protected $phoneNumber = '';

    /**
     * @var \Orkestra\Transactor\Entity\Transaction
     *
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Entity\Transaction", mappedBy="account", cascade={"persist"})
     */
    protected $transactions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->alias;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Set Account Number
     *
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * Get Account Number
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set IP Address
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get IP Address
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Add Transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction
     */
    public function addTransaction(Transaction $transaction)
    {
        if ($transaction->getAccount() !== $this) {
            $transaction->setAccount($this);
        }

        $this->transactions[] = $transaction;
    }

    /**
     * Get Transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set Phone Number
     *
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Get Phone Number
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Sets the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Gets the address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Gets the city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the postal code
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Gets the postal code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Sets the region
     *
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * Gets the region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Sets the country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Gets the country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the alias for this account
     *
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Gets the alias for this account
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
