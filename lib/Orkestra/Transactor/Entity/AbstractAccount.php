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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Orkestra\Common\Entity\AbstractEntity;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * Base class for any Account entity
 *
 * @ORM\Table(name="orkestra_accounts")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "BankAccount"                = "Orkestra\Transactor\Entity\Account\BankAccount",
 *   "CardAccount"                = "Orkestra\Transactor\Entity\Account\CardAccount",
 *   "PointsAccount"              = "Orkestra\Transactor\Entity\Account\PointsAccount",
 *   "SimpleAccount"              = "Orkestra\Transactor\Entity\Account\SimpleAccount",
 *   "SwipedCardAccount"          = "Orkestra\Transactor\Entity\Account\SwipedCardAccount",
 *   "EncryptedSwipedCardAccount" = "Orkestra\Transactor\Entity\Account\EncryptedSwipedCardAccount"
 * })
 */
abstract class AbstractAccount extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="account_number", type="string")
     */
    protected $accountNumber = '';

    /**
     * @var string
     *
     * @ORM\Column(name="last_four", type="string")
     */
    protected $lastFour = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string")
     */
    protected $emailAddress = '';

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
     * @var string
     *
     * @ORM\Column(name="external_person_id", type="string")
     */
    protected $externalPersonId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="external_account_id", type="string")
     */
    protected $externalAccountId = '';

    /**
     * @var TransactionInterface
     *
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Entity\Transaction", mappedBy="account", cascade={"persist"})
     */
    protected $transactions;

    /**
     * @var \Orkestra\Transactor\Entity\Credentials $credentials
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\Credentials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected $credentials;

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
        return (string) ($this->alias ?: sprintf('%s ending with %s', $this->getType(), $this->lastFour));
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
        $this->accountNumber = (string) $accountNumber;
        $this->lastFour = substr((string) $accountNumber,-4);
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
        $this->ipAddress = (string) $ipAddress;
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
    public function addTransaction(TransactionInterface $transaction)
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
        $this->phoneNumber = (string) $phoneNumber;
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
        $this->name = (string) $name;
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
        $this->address = (string) $address;
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
        $this->city = (string) $city;
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
        $this->postalCode = (string) $postalCode;
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
        $this->region = (string) $region;
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
        $this->country = (string) $country;
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
        $this->alias = (string) $alias;
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

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = (string) $emailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $externalAccountId
     */
    public function setExternalAccountId($externalAccountId)
    {
        $this->externalAccountId = (string) $externalAccountId;
    }

    /**
     * @return string
     */
    public function getExternalAccountId()
    {
        return $this->externalAccountId;
    }

    /**
     * @param string $externalPersonId
     */
    public function setExternalPersonId($externalPersonId)
    {
        $this->externalPersonId = (string) $externalPersonId;
    }

    /**
     * @return string
     */
    public function getExternalPersonId()
    {
        return $this->externalPersonId;
    }

    /**
     * @param \Orkestra\Transactor\Entity\Credentials $credentials
     */
    public function setCredentials(Credentials $credentials = null)
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
     * @return string
     */
    public function getLastFour()
    {
        return $this->lastFour;
    }
}
