<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Transactor\NetworkMerchants;

use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Entity\Account\BankAccount\AccountType;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Exception\ValidationException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * ACH transactor for the NetworkMerchants payment processing gateway
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
     * @param TransactionInterface $transaction
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
     * @param TransactionInterface $transaction
     * @param array                $options
     *
     * @return array
     */
    protected function buildParams(TransactionInterface $transaction, array $options = array())
    {
        $credentials = $transaction->getCredentials();

        $params = array(
            'type' => $this->getNmiType($transaction),
            'username' => $credentials->getCredential('username'),
            'password' => $credentials->getCredential('password'),
        );

        if (in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::REFUND))
        ) {
            $params = array_merge($params, array(
                'transactionid' => $transaction->getParent()->getResult()->getExternalId(),
            ));
        } else {
            $account = $transaction->getAccount();

            $names = explode(' ', $account->getName(), 2);
            $firstName = isset($names[0]) ? $names[0] : '';
            $lastName = isset($names[1]) ? $names[1] : '';

            $params = array_merge($params, array(
                'firstname' => $firstName,
                'lastname' => $lastName,
                'address' => $account->getAddress(),
                'city' => $account->getCity(),
                'state' => $account->getRegion(),
                'zip' => $account->getPostalCode(),
                'country' => $account->getCountry(),
                'ipaddress' => $account->getIpAddress(),
                'payment' => 'check',
                'sec_code' => $credentials->getCredential('secCode') ?: 'WEB',
                'account_holder_type' => in_array($account->getAccountType()->getValue(), array(
                    AccountType::PERSONAL_SAVINGS,
                    AccountType::PERSONAL_CHECKING
                )) ? 'personal' : 'business',
                'account_type' => in_array($transaction->getAccount()->getAccountType()->getValue(), array(
                    AccountType::PERSONAL_SAVINGS,
                    AccountType::BUSINESS_SAVINGS
                )) ? 'savings' : 'checking',
                'amount' => $transaction->getAmount(),
                'checkname' => $account->getName(),
                'checkaba' => $account->getRoutingNumber(),
                'checkaccount' => $account->getAccountNumber()
            ));
        }

        return $params;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    protected function configureResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'post_url'   => 'https://secure.networkmerchants.com/api/transact.php',
        ));
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
        foreach (array('checkaccount', 'checkaba') as $key) {
            if (array_key_exists($key, $request)) {
                $request[$key] = '[filtered]';
            }
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
        return 'orkestra.network_merchants.ach';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Network Merchants ACH Gateway';
    }

    /**
     * Creates a new, empty Credentials entity
     *
     * @return \Orkestra\Transactor\Entity\Credentials
     */
    public function createCredentials()
    {
        $credentials = parent::createCredentials();
        $credentials->setCredential('secCode', 'WEB');

        return $credentials;
    }
}
