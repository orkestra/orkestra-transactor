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

use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Exception\ValidationException;
use Orkestra\Transactor\Model\Account\CardAccountInterface;
use Orkestra\Transactor\Model\Account\SwipedCardAccountInterface;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\ResultInterface;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Credit card transactor for the Network Merchants payment processing gateway
 */
class CardTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $supportedNetworks = array(
        NetworkType::CARD,
        NetworkType::SWIPED
    );

    /**
     * @var array
     */
    protected static $supportedTypes = array(
        TransactionType::SALE,
        TransactionType::AUTH,
        TransactionType::CAPTURE,
        TransactionType::CREDIT,
        TransactionType::REFUND,
        TransactionType::VOID,
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
     * @param TransactionInterface $transaction
     * @param array                $options
     *
     * @return ResultInterface
     */
    protected function doTransact(TransactionInterface $transaction, array $options = array())
    {
        $this->validateTransaction($transaction);
        $params = $this->buildParams($transaction, $options);
        $result = $transaction->getResult();
        $result->setTransactor($this);

        $postUrl = $options['post_url'];
        $client = $this->getClient();

        $request = $client->post($postUrl)
            ->addPostFields($params);

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
            $result->setStatus(new ResultStatus((!empty($data['response']) && '2' == $data['response']) ? ResultStatus::DECLINED : ResultStatus::ERROR));
            $result->setMessage(empty($data['responsetext']) ? 'An error occurred while processing the payment. Please try again.' : $data['responsetext']);

            if (!empty($data['transactionid'])) {
                $result->setExternalId($data['transactionid']);
            }
        } else {
            $result->setStatus(new ResultStatus(ResultStatus::APPROVED));
            $result->setExternalId($data['transactionid']);
        }

        $result->setData('request', $params);
        $result->setData('response', $data);

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
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(TransactionType::CAPTURE, TransactionType::REFUND, TransactionType::VOID))) {
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

        if ((!($account instanceof CardAccountInterface) && !($account instanceof SwipedCardAccountInterface))
            || (!($account instanceof SwipedCardAccountInterface) && $transaction->getNetwork() == NetworkType::SWIPED)
        ) {
            throw ValidationException::invalidAccountType($account);
        }

        if (!$account instanceof SwipedCardAccountInterface) {
            if (null === $account->getAccountNumber()) {
                throw ValidationException::missingRequiredParameter('account number');
            } elseif (null === $account->getExpMonth() || null === $account->getExpYear()) {
                throw ValidationException::missingRequiredParameter('card expiration');
            }
        }
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return string
     */
    protected function getNmiType(TransactionInterface $transaction)
    {
        switch ($transaction->getType()->getValue()) {
            case TransactionType::SALE:
                return 'sale';
            case TransactionType::AUTH:
                return 'auth';
            case TransactionType::CAPTURE:
                return 'capture';
            case TransactionType::CREDIT:
                return 'credit';
            case TransactionType::REFUND:
                return 'refund';
            case TransactionType::VOID:
                return 'void';
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
            TransactionType::CAPTURE,
            TransactionType::REFUND,
            TransactionType::VOID))
        ) {
            $params = array_merge($params, array(
                'transactionid' => $transaction->getParent()->getResult()->getExternalId(),
            ));
        } else {
            $account = $transaction->getAccount();

            if ($account instanceof SwipedCardAccountInterface) {
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

        if ($transaction->getType()->getValue() != TransactionType::VOID) {
            $params['amount'] = $transaction->getAmount();
        }

        return $params;
    }

    /**
     * Filter the given result
     *
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    protected function filterResult(ResultInterface $result)
    {
        $request = $result->getData('request') ?: array();
        foreach (array('ccnumber', 'cvv', 'track_1', 'track_2', 'track_3') as $key) {
            if (array_key_exists($key, $request)) {
                $request[$key] = '[filtered]';
            }
        }

        $result->setData('request', $request);

        return $result;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    protected function configureResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'enable_avs' => false,
            'enable_cvv' => false,
            'post_url'   => 'https://secure.networkmerchants.com/api/transact.php',
        ));
    }

    /**
     * @return \Guzzle\Http\Client
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = new Client();
        }

        return $this->client;
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
        $credentials->setCredentials(array(
            'username' => null,
            'password' => null,
        ));

        return $credentials;
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
