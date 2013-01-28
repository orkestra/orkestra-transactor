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

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Exception\ValidationException;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Credit card transactor for the Network Merchants payment processing gateway
 */
class CardTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $_supportedNetworks = array(
        Transaction\NetworkType::CARD,
        Transaction\NetworkType::SWIPED
    );

    /**
     * @var array
     */
    protected static $_supportedTypes = array(
        Transaction\TransactionType::SALE,
        Transaction\TransactionType::AUTH,
        Transaction\TransactionType::CAPTURE,
        Transaction\TransactionType::CREDIT,
        Transaction\TransactionType::REFUND,
        Transaction\TransactionType::VOID,
    );

    /**
     * @var \Guzzle\Http\Client
     */
    private $client;

    /**
     * Constructor
     *
     * @param \Guzzle\Http\Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * Transacts the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array                                   $options
     *
     * @return \Orkestra\Transactor\Entity\Result
     */
    public function _doTransact(Transaction $transaction, array $options = array())
    {
        $this->_validateTransaction($transaction);
        $params = $this->_buildParams($transaction, $options);
        $result = $transaction->getResult();
        $result->setTransactor($this);

        $postUrl = $options['postUrl'];
        $client = $this->getClient();

        $request = $client->post($postUrl)
            ->addPostFields($params);
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 3);

        try {
            $response = $request->send();
            $data = array();
            parse_str($response->getBody(true), $data);
        } catch (BadResponseException $e) {
            $data = array(
                'response' => '3',
                'message' => $e->getMessage()
            );
        }

        if (empty($data['response']) || '1' != $data['response']) {
            $result->setStatus(new Result\ResultStatus((!empty($data['response']) && '2' == $data['response']) ? Result\ResultStatus::DECLINED : Result\ResultStatus::ERROR));
            $result->setMessage(empty($data['responsetext']) ? 'An error occurred while processing the payment. Please try again.' : $data['responsetext']);

            if (!empty($data['transactionid'])) {
                $result->setExternalId($data['transactionid']);
            }
        } else {
            $result->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));
            $result->setExternalId($data['transactionid']);
        }

        $result->setData('request', $params);
        $result->setData('response', $data);

        return $result;
    }

    /**
     * Validates the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     *
     * @throws \Orkestra\Transactor\Exception\ValidationException
     */
    protected function _validateTransaction(Transaction $transaction)
    {
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(Transaction\TransactionType::CAPTURE, Transaction\TransactionType::REFUND, Transaction\TransactionType::VOID))) {
            throw ValidationException::parentTransactionRequired();
        }

        $credentials = $transaction->getCredentials();

        if (!$credentials) {
            throw ValidationException::missingCredentials();
        } elseif (null === $credentials->getCredential('username') || null === $credentials->getCredential('password')) {
            throw ValidationException::missingRequiredParameter('username or password');
        }

        $account = $transaction->getAccount();

        if (!$account) {
            throw ValidationException::missingAccountInformation();
        }

        if ((!($account instanceof CardAccount) && !($account instanceof SwipedCardAccount))
            || (!($account instanceof SwipedCardAccount) && $transaction->getNetwork() == Transaction\NetworkType::SWIPED)
        ) {
            throw ValidationException::invalidAccountType($account);
        }

        if (null === $account->getAccountNumber()) {
            throw ValidationException::missingRequiredParameter('account number');
        } elseif (null === $account->getExpMonth() || null === $account->getExpYear()) {
            throw ValidationException::missingRequiredParameter('card expiration');
        }
    }

    /**
     * @param  \Orkestra\Transactor\Entity\Transaction $transaction
     * @return string
     */
    protected function _getNmiType(Transaction $transaction)
    {
        switch ($transaction->getType()->getValue()) {
            case Transaction\TransactionType::SALE:
                return 'sale';
            case Transaction\TransactionType::AUTH:
                return 'auth';
            case Transaction\TransactionType::CAPTURE:
                return 'capture';
            case Transaction\TransactionType::CREDIT:
                return 'credit';
            case Transaction\TransactionType::REFUND:
                return 'refund';
            case Transaction\TransactionType::VOID:
                return 'void';
        }
    }

    /**
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array                                   $options
     *
     * @return array
     */
    protected function _buildParams(Transaction $transaction, array $options = array())
    {
        $credentials = $transaction->getCredentials();

        $params = array(
            'type' => $this->_getNmiType($transaction),
            'username' => $credentials->getCredential('username'),
            'password' => $credentials->getCredential('password'),
        );

        if (in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::CAPTURE,
            Transaction\TransactionType::REFUND,
            Transaction\TransactionType::VOID))
        ) {
            $params = array_merge($params, array(
                'transactionid' => $transaction->getParent()->getResult()->getExternalId(),
            ));
        } else {
            $account = $transaction->getAccount();

            if ($account instanceof SwipedCardAccount) {
                $params = array_merge($params, array(
                    'track_1' => $account->getTrackOne(),
                    'track_2' => $account->getTrackTwo(),
                    'track_3' => $account->getTrackThree()
                ));
            } else {
                $params = array_merge($params, array(
                    'ccnumber' => $account->getAccountNumber(),
                    'ccexp' => $account->getExpMonth()->getLongMonth() . $account->getExpYear()->getShortYear()
                ));

                if (isset($options['enable_cvv']) && true === $options['enable_cvv']) {
                    $params['cvv'] = $account->getCvv();
                }

                if (isset($options['enable_avs']) && true === $options['enable_avs']) {
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
                        'ipaddress' => $account->getIpAddress()
                    ));
                }
            }
        }

        if ($transaction->getType()->getValue() != Transaction\TransactionType::VOID) {
            $params['amount'] = $transaction->getAmount();
        }

        return $params;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    protected function configureResolver(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'enable_avs' => false,
            'enable_cvv' => false,
            'postUrl'    => 'https://secure.networkmerchants.com/api/transact.php',
        ));
    }

    /**
     * @return \Guzzle\Http\Client
     */
    private function getClient()
    {
        if (null === $this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    /**
     * Returns the internally used type of this Transactor
     *
     * @return string
     */
    public function getType()
    {
        return 'orkestra.network_merchants.card';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Network Merchants Credit Card Gateway';
    }
}
