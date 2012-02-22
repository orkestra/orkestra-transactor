<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM,
    Orkestra\Common\Entity\EntityBase;

use Orkestra\Transactor\Exception\TransactException;

/**
 * Transactor Base
 *
 * Base class for all Transactors
 *
 * @ORM\Table(name="orkestra_transactors")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "NmiCardTransactor" = "Orkestra\Transactor\Entity\Transactor\NmiCardTransactor"
 * })
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class TransactorBase extends EntityBase
{
    /**
     * @var array $_supportedTypes An array of TransactionType constants
     */
    protected static $_supportedTypes = array();
    
    /**
     * @var string $name
     * @ORM\Column(name="name", type="string")
     */
    protected $name;
    
    /**
     * @var string $description
     * @ORM\Column(name="description", type="string")
     */
    protected $description = '';
    
    /**
     * @var array $credentials
     * @ORM\Column(name="credentials", type="array")
     */
    protected $credentials = array();
    
    /**
     * Transact
     *
     * This method should be overridden by subclass
     *
     * @return Orkestra\Transactor\Entity\TransactionResult
     */
    public function transact(Transaction $transaction, $options = array())
    {
        if ($transaction->isTransacted()) {
            throw TransactException::transactionAlreadyProcessed();
        }
        else if (!$this->supports($transaction->getType())) {
            throw TransactException::unsupportedTransactionType($transaction->getType());
        }
    }
    
    /**
     * Supports
     *
     * Returns true if this Transactor supports a given Transaction type
     *
     * @param Orkestra\Transactor\Entity\TransactionType $type A valid Transaction type
     * @return boolean True if supported
     */
    public function supports(TransactionType $type)
    {
        if (!in_array($type->getValue(), static::$_supportedTypes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->getName(), $this->getType());
    }
    
    /**
     * Get Type
     *
     * Returns a string containing the type of transactor this is
     *
     * @return string
     */
    abstract public function getType();
    
    /**
     * Set Name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set Description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description ?: '';
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set Credential
     *
     * @param string $key The key of which credential to set
     * @param mixed $value
     */
    public function setCredential($key, $value)
    {
        $this->credentials[$key] = $value;
    }
    
    /**
     * Get Credential
     *
     * @param string $key The key of which credential to get
     * @return mixed
     */
    public function getCredential($key)
    {
        return empty($this->credentials[$key]) ? null : $this->credentials[$key];
    }
    
    /**
     * Set Credentials
     *
     * @param array $credentials An array of credentials
     */
    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;
    }
    
    /**
     * Get Credentials
     *
     * @param array An array of credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
}