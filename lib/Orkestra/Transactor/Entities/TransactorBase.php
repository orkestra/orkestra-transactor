<?php

namespace Orkestra\Transactor\Entities;

use Doctrine\ORM\Mapping as ORM,
    Orkestra\Transactor\Exceptions\TransactException;

/**
 * Transactor Base
 *
 * Base class for all Transactors
 *
 * @ORM\Table(name="orkestra_transactors")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "NmiTransactor" = "Orkestra\Transactor\Entities\Transactor\NmiTransactor"
 * })
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class TransactorBase extends EntityBase
{
    /**
     * @var array $_supportedTypes An array of Transaction::TYPE_* constants
     */
    protected static $_supportedTypes = array();
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name;
    
    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string")
     */
    protected $description = '';
    
    /**
     * @var array $credentials
     *
     * @ORM\Column(name="credentials", type="array")
     */
    protected $credentials = array();
    
    /**
     * Transact
     *
     * @return Orkestra\Transactor\TransactionResult
     */
    public function transact(Transaction $transaction)
    {
        if ($transaction->isTransacted()) {
            throw new TransactException('This transaction has already been processed.');
        }
        else if (!$this->supports($transaction->getType())) {
            throw new TransactException('The given transaction is not supported by this Transactor');
        }
    }
    
    /**
     * Supports
     *
     * Returns true if this Transactor supports a given Transaction type
     *
     * @param mixed $type A valid Transaction type
     * @return boolean True if supported
     */
    public function supports($type)
    {
        if (!in_array($type, Transaction::getTypes())) {
            throw new \InvalidArgumentException(sprintf('Invalid transation type: %s', $type));
        }
        else if (!in_array($type, static::getTypes())) {
            return false;
        }
        
        return true;
    }
    
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
        $this->description = $description;
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
}