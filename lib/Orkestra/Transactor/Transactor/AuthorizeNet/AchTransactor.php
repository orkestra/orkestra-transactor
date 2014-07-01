<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Transactor\AuthorizeNet;

use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Exception\ValidationException;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * ACH transactor for the Authorize.net payment processing gateway
 */
class AchTransactor extends CardTransactor
{
    /**
     * @var array
     */
    protected static $supportedNetworks = array(
        Transaction\NetworkType::ACH,
    );

    /**
     * @var array
     */
    protected static $supportedTypes = array(
        Transaction\TransactionType::SALE,
        Transaction\TransactionType::REFUND
    );

    /**
     * Validates the given transaction
     *
     * @param \Orkestra\Transactor\Model\TransactionInterface $transaction
     *
     * @throws \Orkestra\Transactor\Exception\ValidationException
     */
    protected function validateTransaction(TransactionInterface $transaction)
    {
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::REFUND))
        ) {
            throw ValidationException::parentTransactionRequired();
        }

        $credentials = $transaction->getCredentials();

        if (!$credentials) {
            throw ValidationException::missingCredentials();
        } elseif (null === $credentials->getCredential('username') || null === $credentials->getCredential('password')) {
            throw ValidationException::missingRequiredParameter('username or password');
        }

        /** @var BankAccount $account */
        $account = $transaction->getAccount();

        if (!$account || !$account instanceof BankAccount) {
            throw ValidationException::missingAccountInformation();
        }

        if (null === $account->getAccountNumber()) {
            throw ValidationException::missingRequiredParameter('account number');
        } elseif (null === $account->getRoutingNumber()) {
            throw ValidationException::missingRequiredParameter('routing number');
        } elseif (null === $account->getAccountType()) {
            throw ValidationException::missingRequiredParameter('account type');
        }
    }

    /**
     * Filter the given result
     *
     * @param Result $result
     *
     * @return Result
     */
    protected function filterResult(Result $result)
    {
        $request = $result->getData('request') ?: array();

        if (!is_array($request)) {
            $request = $this->removeNS($request);
            $request = $this->serializer->decode($request, 'xml');
            $request['transactionRequest']['payment']['bankAccount'] = '[filtered]';
        }

        $result->setData('request', $request);

        return $result;
    }

    /**
     * Returns the internally used type of this Transactor
     *
     * @return string
     */
    public function getType()
    {
        return 'orkestra.authorize_net.ach';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Authorize.net ACH Gateway';
    }
}
