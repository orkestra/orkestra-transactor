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
    protected static $_supportedTypes = array();
    
    protected $credentials = array();
    
    protected $name;
    
    protected $description;
    
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
}