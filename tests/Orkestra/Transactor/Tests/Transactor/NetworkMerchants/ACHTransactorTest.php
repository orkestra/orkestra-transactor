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

use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Entity\Account\BankAccount\AccountType;
use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\Transactor\NetworkMerchants\AchTransactor;
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
class ACHTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = new AchTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::SWIPED)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CASH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::POINTS)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CHECK)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new AchTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::SALE)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::AUTH)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CAPTURE)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CREDIT)));
         $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::REFUND)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::VOID)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::QUERY)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::UPDATE)));
    }

    public function testSaleSuccess()
    {
        $transactor = $this->getTransactor('response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    public function testSaleError()
    {
        $transactor = $this->getTransactor('response=3&responsetext=Invalid Bank Account# REFID:330352367&authcode=&transactionid=&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=300');
        $transaction = $this->getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Invalid Bank Account# REFID:330352367', $result->getMessage());
        $this->assertEquals('', $result->getExternalId());
    }

    public function testSaleDecline()
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


    protected function getTransactor($expectedResponse = '', $code = 200)
    {
        $client = new Client();
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response($code, null, $expectedResponse));
        $client->addSubscriber($plugin);

        return new AchTransactor($client);
    }

    /**
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    protected function getTransaction()
    {
        $account = new BankAccount();
        $account->setAccountNumber('123123123');
        $account->setRoutingNumber('123123123');
        $account->setAccountType(new AccountType(BankAccount\AccountType::PERSONAL_CHECKING));

        $credentials = new Credentials();
        $credentials->setCredential('username', 'demo');
        $credentials->setCredential('password', 'password');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }

}
