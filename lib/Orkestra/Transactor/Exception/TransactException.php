<?php

namespace Orkestra\Transactor\Exception;

/**
 * Transact Exception
 *
 * @package Orkestra
 * @subpackage Transactor
 */
class TransactException extends \Exception
{
    /**
     * Transaction Already Processed
     *
     * Occurs when an attempt is made to process an already transacted transaction.
     *
     * @return Orkestra\Transactor\Exception\TransactException
     */
    public static function transactionAlreadyProcessed()
    {
        return new self('This transaction has already been processed');
    }
    
    /**
     * Unsupported Transaction Type
     *
     * Occurs when an attempt is made to process a transaction of a type not supported by the 
     * given Transactor.
     *
     * @param string $givenType
     * @return Orkestra\Transactor\Exception\TransactException
     */
    public static function unsupportedTransactionType($givenType)
    {
        return new self(sprintf('Transaction type "%s" is not supported by this Transactor', $givenType));
    }
    
    /**
     * Invalid Transaction Type
     *
     * Occurs when an invalid transaction type is used.
     *
     * @return Orkestra\Transactor\Exception\TransactException
     */
    public static function invalidTransactionType($givenType)
    {
        return new self(sprintf('Invalid transaction type: %s', $givenType));
    }
}