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
 * @ORM\DiscriminatorMap({"Test" = "Orkestra\Transactor\TestTransactor"})
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class TransactorBase
{
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
        if (!empty($transaction->getResult())) {
            throw new TransactException('This transaction has already been processed.');
        }
    }
}