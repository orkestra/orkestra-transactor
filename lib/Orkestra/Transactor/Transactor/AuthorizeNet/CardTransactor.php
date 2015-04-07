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

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Exception\ValidationException;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Credit card transactor for the Authorize.net payment processing gateway
 */
class CardTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $supportedNetworks = array(
        Transaction\NetworkType::CARD,
        Transaction\NetworkType::SWIPED
    );

    /**
     * @var array
     */
    protected static $supportedTypes = array(
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
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \Guzzle\Http\Client $client
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $transactionNormalizer
     */
    public function __construct(Client $client = null, NormalizerInterface $transactionNormalizer)
    {
        $this->client = $client;
        $this->serializer = new Serializer(array($transactionNormalizer), array(new XmlEncoder('createTransactionRequest')));
    }

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
        $params = $this->buildParams($transaction, $options);
        $result = $transaction->getResult();
        $result->setTransactor($this);

        $postUrl = $options['post_url'];
        $client = $this->getClient();

        $request = $client->post($postUrl, array('Content-Type' => 'application/xml'), $params);

        $type = $request->getHeader('Content-Type');

        try {
            $response = $request->send();
            $data = (string) $response->getBody(true);
            $data = $this->removeNS($data);
            $data = $this->serializer->decode($data, 'xml');
        } catch (BadResponseException $e) {
            $data = array(
                'response' => '3',
                'message' => $e->getMessage()
            );
        }

        if ($data['messages']['resultCode'] != 'Ok') {
            $result->setStatus(new Result\ResultStatus((!empty($data['response']) && '2' == $data['response']) ? Result\ResultStatus::DECLINED : Result\ResultStatus::ERROR));
            $result->setMessage(empty($data['responsetext']) ? 'An error occurred while processing the payment. Please try again.' : $data['responsetext']);

            if (isset($data['transactionResponse']) && !empty($data['transactionResponse']['transId'])) {
                $result->setExternalId($data['transactionResponse']['transId']);
            }
        } else {
            $result->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));

            $result->setExternalId($data['transactionResponse']['transId']);
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
    protected function validateTransaction(Transaction $transaction)
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

        if (!$account instanceof SwipedCardAccount) {
            if (null === $account->getAccountNumber()) {
                throw ValidationException::missingRequiredParameter('account number');
            } elseif (null === $account->getExpMonth() || null === $account->getExpYear()) {
                throw ValidationException::missingRequiredParameter('card expiration');
            }
        }
    }

    /**
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array                                   $options
     *
     * @return array
     */
    protected function buildParams(Transaction $transaction, array $options = array())
    {
        return $this->serializer->serialize($transaction, 'xml', array('test' => $options['test']));
    }

    protected function removeNS($data) {
        return preg_replace('/xmlns="[^"]*"/', '', $data);
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
        $request = $this->removeNS($request);
        $request = $this->serializer->decode($request, 'xml');

        $ccInfo = $request['transactionRequest']['payment']['creditCard'];
        foreach (array('cardNumber', 'cardCode', 'track1', 'track2') as $key) {
            if (array_key_exists($key, $ccInfo)) {
                $ccInfo[$key] = '[filtered]';
            }
        }

        $request['transactionRequest']['payment']['creditCard'] = $ccInfo;

        $result->setData('request', $request);

        return $result;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    protected function configureResolver(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'enable_avs' => false,
            'enable_cvv' => false,
            'test' => false,
            'post_url'   => 'https://api.authorize.net/xml/v1/request.api',
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
        return 'orkestra.authorize_net.card';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Authorize.net Gateway';
    }
}
