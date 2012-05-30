<?php

namespace Orkestra\Transactor\Exception;

/**
 * An exception that occurs when dealing with Transactors
 */
class TransactorException extends \Exception
{
    /**
     * Occurs when an attempt is made to process an already transacted transaction.
     *
     * @return \Orkestra\Transactor\Exception\TransactorException
     */
    public static function transactionAlreadyProcessed()
    {
        return new self('This transaction has already been processed');
    }

    /**
     * Occurs when an attempt is made to process a transaction of a type not supported by the
     * given Transactor.
     *
     * @param string $type
     *
     * @return \Orkestra\Transactor\Exception\TransactorException
     */
    public static function unsupportedTransactionType($type)
    {
        return new self(sprintf('Transaction type "%s" is not supported by this Transactor', $type ?: 'NULL'));
    }

    /**
     * Occurs when an attempt is made to process a transaction on a network not supported by the
     * given Transactor.
     *
     * @param string $network
     *
     * @return \Orkestra\Transactor\Exception\TransactorException
     */
    public static function unsupportedTransactionNetwork($network)
    {
        return new self(sprintf('Transaction network "%s" is not supported by this Transactor', $network ?: 'NULL'));
    }

    /**
     * Occurs when an invalid transaction type is used.
     *
     * @param string $givenType
     *
     * @return \Orkestra\Transactor\Exception\TransactorException
     */
    public static function invalidTransactionType($givenType)
    {
        return new self(sprintf('Invalid transaction type: %s', $givenType));
    }

    /**
     * Occurs when an attempt to get an unknown transactor is made.
     *
     * @param string $name
     *
     * @return \Orkestra\Transactor\Exception\TransactorException
     */
    public static function transactorNotRegistered($name)
    {
        return new self(sprintf('Unknown Transactor: %s', $name));
    }
}
