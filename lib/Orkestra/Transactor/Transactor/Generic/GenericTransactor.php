<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Transactor\Generic;

use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Exception\ValidationException;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\ResultInterface;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * Handles in person cash or check transactions
 */
class GenericTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $supportedNetworks = array(
        NetworkType::CASH,
        NetworkType::CHECK
    );

    /**
     * @var array
     */
    protected static $supportedTypes = array(
        TransactionType::SALE,
        TransactionType::CREDIT,
        TransactionType::REFUND,
    );

    /**
     * Transacts the given transaction
     *
     * @param TransactionInterface $transaction
     * @param array                $options
     *
     * @return ResultInterface
     */
    protected function doTransact(TransactionInterface $transaction, array $options = array())
    {
        $this->_validateTransaction($transaction);

        $result = $transaction->getResult();
        $result->setTransactor($this);

        $result->setStatus(new ResultStatus(ResultStatus::APPROVED));

        return $result;
    }

    /**
     * Validates the given transaction
     *
     * @param TransactionInterface $transaction
     *
     * @throws \Orkestra\Transactor\Exception\ValidationException
     */
    protected function _validateTransaction(TransactionInterface $transaction)
    {
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(
            TransactionType::CAPTURE,
            TransactionType::REFUND
        ))) {
            throw ValidationException::parentTransactionRequired();
        }
    }

    /**
     * Creates a new, empty Credentials entity
     *
     * @return \Orkestra\Transactor\Entity\Credentials
     */
    public function createCredentials()
    {
        $credentials = new Credentials();
        $credentials->setTransactor($this);

        return $credentials;
    }

    /**
     * Returns the internally used type of this Transactor
     *
     * @return string
     */
    public function getType()
    {
        return 'orkestra.generic';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Generic Transactor';
    }
}
