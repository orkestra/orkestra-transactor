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
use Orkestra\Transactor\Entity\Account\PointsAccount;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Exception\ValidationException;

/**
 * Handles Points transactions
 */
class PointsTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $supportedNetworks = array(
        Transaction\NetworkType::POINTS
    );

    /**
     * @var array
     */
    protected static $supportedTypes = array(
        Transaction\TransactionType::SALE,
        Transaction\TransactionType::CREDIT,
        Transaction\TransactionType::REFUND,
    );

    /**
     * Transacts the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array                                   $options
     *
     * @return \Orkestra\Transactor\Entity\Result
     */
    protected function doTransact(Transaction $transaction, array $options = array())
    {
        $this->validateTransaction($transaction);

        $account = $transaction->getAccount();
        $result = $transaction->getResult();
        $result->setTransactor($this);

        $adjustment = $transaction->getAmount();
        if (Transaction\TransactionType::SALE === $transaction->getType()->getValue()) {
            if ($transaction->getAmount() > $account->getBalance()) {
                $result->setStatus(new Result\ResultStatus(Result\ResultStatus::DECLINED));
                $result->setMessage('Amount exceeds account balance');

                return $result;
            }

            $adjustment *= -1; // Negate the adjustment
        }

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));
        $account->adjustBalance($adjustment);

        return $result;
    }

    /**
     * Validates the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     *
     * @throws \Orkestra\Transactor\Exception\ValidationException
     */
    protected function validateTransaction(Transaction $transaction)
    {
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::REFUND
        ))) {
            throw ValidationException::parentTransactionRequired();
        } elseif (!$transaction->getAccount() instanceof PointsAccount) {
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
