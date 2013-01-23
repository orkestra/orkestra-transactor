<?php

namespace Orkestra\Transactor;

use Orkestra\Transactor\Entity\Result\ResultStatus;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Exception\TransactorException;

/**
 * Base class for any Transactor
 */
abstract class AbstractTransactor implements TransactorInterface
{
    /**
     * @var array $_supportedNetworks An array of NetworkType constants
     */
    protected static $_supportedNetworks = array();

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
        } elseif (!$this->supportsType($transaction->getType())) {
            throw TransactorException::unsupportedTransactionType($transaction->getType());
        } elseif (!$this->supportsNetwork($transaction->getNetwork())) {
            throw TransactorException::unsupportedTransactionNetwork($transaction->getNetwork());
        }

        $result = $transaction->getResult();

        try {
            $this->_doTransact($transaction, $options);
        } catch (\Exception $e) {
            $result->setStatus(new ResultStatus(ResultStatus::ERROR));
            $result->setMessage('An internal error occurred while processing the transaction.');
            $result->setData('message', $e->getMessage());
            $result->setData('trace', $e->getTraceAsString());
        }

        return $result;
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
     * Returns true if this Transactor supports a given Transaction type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType|null $type
     * @return boolean True if supported
     */
    public function supportsType(Transaction\TransactionType $type = null)
    {
        return in_array((null === $type ? null : $type->getValue()), static::$_supportedTypes);
    }

    /**
     * Returns true if this Transactor supports a given Network type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\NetworkType|null $network
     *
     * @return boolean True if supported
     */
    public function supportsNetwork(Transaction\NetworkType $network = null)
    {
        return in_array((null === $network ? null : $network->getValue()), static::$_supportedNetworks);
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
