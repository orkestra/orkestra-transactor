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
use Orkestra\Transactor\Model\Account\PointsAccountInterface;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\ResultInterface;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * Handles Points transactions
 */
class PointsTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $supportedNetworks = array(
        NetworkType::POINTS
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
        $this->validateTransaction($transaction);

        $account = $transaction->getAccount();
        $result = $transaction->getResult();
        $result->setTransactor($this);

        $adjustment = $transaction->getAmount();
        if (TransactionType::SALE === $transaction->getType()->getValue()) {
            if ($transaction->getAmount() > $account->getBalance()) {
                $result->setStatus(new ResultStatus(ResultStatus::DECLINED));
                $result->setMessage('Amount exceeds account balance');

                return $result;
            }

            $adjustment *= -1; // Negate the adjustment
        }

        $result->setStatus(new ResultStatus(ResultStatus::APPROVED));
        $account->adjustBalance($adjustment);

        return $result;
    }

    /**
     * Validates the given transaction
     *
     * @param TransactionInterface $transaction
     *
     * @throws \Orkestra\Transactor\Exception\ValidationException
     */
    protected function validateTransaction(TransactionInterface $transaction)
    {
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(
            TransactionType::REFUND
        ))) {
            throw ValidationException::parentTransactionRequired();
        } elseif (!$transaction->getAccount() instanceof PointsAccountInterface) {
            throw ValidationException::invalidAccountType($transaction->getAccount());
        }

        $transaction->setAmount((int) $transaction->getAmount());
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
        return 'orkestra.generic.points';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Points Transactor';
    }
}
