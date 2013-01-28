<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Transactor\NetworkMerchants;

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\Transactor\NetworkMerchants\CardTransactor;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;
use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

/**
 * Unit tests for the Network Merchants Card Transactor
 *
 * @group orkestra
 * @group transactor
 */
class CardTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = new CardTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::SWIPED)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CASH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::POINTS)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CHECK)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new CardTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::SALE)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::AUTH)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CAPTURE)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CREDIT)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::REFUND)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::VOID)));

        // Unsupported
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::QUERY)));
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::UPDATE)));
    }

    public function testCardSaleSuccess()
    {
        $transactor = $this->getTransactor('response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
        $this->assertArrayNotHasKey('track_1', $result->getData('request'));
        $this->assertArrayNotHasKey('track_2', $result->getData('request'));
        $this->assertArrayNotHasKey('track_3', $result->getData('request'));
    }

    public function testCardSaleError()
    {
        $transactor = $this->getTransactor('response=3&responsetext=Invalid Credit Card Number REFID:330352367&authcode=&transactionid=&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=300');
        $transaction = $this->getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Invalid Credit Card Number REFID:330352367', $result->getMessage());
        $this->assertEquals('', $result->getExternalId());
    }

    public function testCardSaleDecline()
    {
        $transactor = $this->getTransactor('response=2&responsetext=DECLINE&authcode=&transactionid=54321&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=200');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('DECLINE', $result->getMessage());
        $this->assertEquals('54321', $result->getExternalId());
    }

    public function testHttpError()
    {
        $transactor = $this->getTransactor('', 503);
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('An error occurred while processing the payment. Please try again.', $result->getMessage());
        $this->assertEmpty($result->getExternalId());
    }

    public function testNonSwipeOnSwipeNetworkValidation()
    {
        $transaction = $this->getTransaction();
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::SWIPED));

        $transactor = $this->getTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('An internal error occurred while processing the transaction.', $result->getMessage());
        $this->assertEmpty($result->getExternalId());
        $this->assertEquals('Validation failed: invalid account type: Credit Card', $result->getData('message'));
    }

    public function testSwipedSalesSuccess()
    {
        $transactor = $this->getTransactor('response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100');
        $transaction = $this->getSwipedTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
        $this->assertInternalType('array', $result->getData('request'));
        $this->assertArrayHasKey('track_1', $result->getData('request'));
        $this->assertArrayHasKey('track_2', $result->getData('request'));
        $this->assertArrayHasKey('track_3', $result->getData('request'));
    }

    public function testAvsAndCvvDisabledByDefault()
    {
        $transactor = $this->getTransactor('response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $request = $result->getData('request');

        $this->assertArrayNotHasKey('cvv', $request);
        $this->assertArrayNotHasKey('firstname', $request);
        $this->assertArrayNotHasKey('lastname', $request);
        $this->assertArrayNotHasKey('address', $request);
    }

    public function testEnableAvs()
    {
        $transactor = $this->getTransactor('response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction, array('enable_avs' => true));

        $request = $result->getData('request');

        $this->assertArrayNotHasKey('cvv', $request);
        $this->assertArrayHasKey('firstname', $request);
        $this->assertArrayHasKey('lastname', $request);
        $this->assertArrayHasKey('address', $request);
        $this->assertArrayHasKey('city', $request);
        $this->assertArrayHasKey('state', $request);
        $this->assertArrayHasKey('zip', $request);
        $this->assertArrayHasKey('country', $request);
        $this->assertArrayHasKey('ipaddress', $request);
    }

    public function testEnableCvv()
    {
        $transactor = $this->getTransactor('response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction, array('enable_cvv' => true));

        $request = $result->getData('request');

        $this->assertArrayHasKey('cvv', $request);
        $this->assertArrayNotHasKey('firstname', $request);
        $this->assertArrayNotHasKey('lastname', $request);
        $this->assertArrayNotHasKey('address', $request);
    }

    protected function getTransactor($expectedResponse = '', $code = 200)
    {
        $client = new Client();
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response($code, null, $expectedResponse));
        $client->addSubscriber($plugin);

        return new CardTransactor($client);
    }

    /**
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    protected function getTransaction()
    {
        $account = new CardAccount();
        $account->setAccountNumber('4111111111111111');
        $account->setExpMonth(new Month(10));
        $account->setExpYear(new Year(2010));

        $credentials = new Credentials();
        $credentials->setCredential('username', 'demo');
        $credentials->setCredential('password', 'password');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }

    protected function getSwipedTransaction()
    {
        $account = new SwipedCardAccount();
        $account->setTrackOne('%4111111111111111^SOMMER/T.                 ^12011200000000000000**XXX******?*');

        $credentials = new Credentials();
        $credentials->setCredential('username', 'demo');
        $credentials->setCredential('password', 'password');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::SWIPED));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }
}
