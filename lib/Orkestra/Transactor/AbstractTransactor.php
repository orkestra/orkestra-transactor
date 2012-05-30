<?php

namespace Orkestra\Transactor;

use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Exception\TransactorException;

/**
 * Base class for any Transactor
 */
abstract class AbstractTransactor implements TransactorInterface
{
    /**
     * @var array $_supportedTypes An array of TransactionType constants
     */
    protected static $_supportedTypes = array();

    /**
     * Transacts the given transaction
     *
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array $options
     *
     * @throws \Orkestra\Transactor\Exception\TransactorException
     * @return \Orkestra\Transactor\Entity\Result
     */
    public function transact(Transaction $transaction, $options = array())
    {
        if ($transaction->isTransacted()) {
            throw TransactorException::transactionAlreadyProcessed();
        }
        else if (!$this->supports($transaction->getType())) {
            throw TransactorException::unsupportedTransactionType($transaction->getType());
        }

        return $this->_doTransact($transaction, $options);
    }

    /**
     * Transacts the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array $options
     *
     * @return \Orkestra\Transactor\Entity\Result
     */
    abstract protected function _doTransact(Transaction $transaction, $options = array());

    /**
     * Supports
     *
     * Returns true if this Transactor supports a given Transaction type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType $type A valid Transaction type
     * @return boolean True if supported
     */
    public function supports(Transaction\TransactionType $type)
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
}
