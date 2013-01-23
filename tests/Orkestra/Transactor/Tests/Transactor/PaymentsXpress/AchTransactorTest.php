<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Transactor\PaymentsXpress;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

use Orkestra\Transactor\Transactor\PaymentsXpress\AchTransactor;
use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

/**
 * Unit tests for the Payments Xpress ACH Transactor
 *
 * @group orkestra
 * @group transactor
 * @group ach
 */
class AchTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = new AchTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new AchTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::SALE)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::AUTH)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CAPTURE)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CREDIT)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::REFUND)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::VOID)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::QUERY)));
        // $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::UPDATE)));
    }

    public function testResponseError()
    {
        $transactor = $this->getTransactor('', 503);
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Client Error: An error occurred while contacting the PaymentsXpress system', $result->getMessage());
    }

    public function testSaleSuccess()
    {
        $transactor = $this->getTransactor('{"CommandStatus":"Approved","Description":"","ErrorInformation":"","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"56789","ResponseCode":"100"}');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::PENDING, $result->getStatus()->getValue());
        $this->assertEquals('56789', $result->getExternalId());

        return $transaction;
    }

    public function testSaleError()
    {
        $transactor = $this->getTransactor('{"CommandStatus":"Error","Description":"Internal Gateway Error","ErrorInformation":"","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"54321","ResponseCode":"100"}');
        $transaction = $this->getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Internal Gateway Error', $result->getMessage());
        $this->assertEquals('54321', $result->getExternalId());
    }

    public function testSaleDecline()
    {
        $transactor = $this->getTransactor('{"CommandStatus":"Declined","Description":"Invalid Gateway Credentials","ErrorInformation":"Provider_Credentials","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"12345","ResponseCode":"100"}');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('Invalid Gateway Credentials: Provider_Credentials', $result->getMessage());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithNoUpdateSetsParentStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertNotSame($result->getStatus(), $parent->getResult()->getStatus());
        $this->assertEquals(Result\ResultStatus::PENDING, $result->getStatus()->getValue());
        $this->assertEquals($result->getStatus()->getValue(), $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithScheduledStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('12345,,,Cancelled,02/20/2003 02:59:00,Cancelled,,,,
56789,,,Created,02/20/2003 03:00:00,Scheduled,,,,
45666,,,Cancelled,02/20/2003 03:01:00,Cancelled,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::PENDING, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithCancelledStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('56789,,,Cancelled,02/20/2003 03:00:00,Cancelled,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::CANCELLED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithProcessedStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('56789,,,Submitted,02/20/2003 03:00:00,In-Process,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::PROCESSED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithApprovedStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('56789,,,Cleared,02/20/2003 03:00:00,Cleared,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithDeclinedStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('56789,,,Rejected,02/20/2003 03:00:00,Failed Verification,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithChargedBackStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('56789,,,Charged Back,02/20/2003 03:00:00,Charged Back,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::CHARGED_BACK, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithHoldBackStatus(Transaction $parent)
    {
        $transactor = $this->getTransactor('56789,,,Held by Merchant,02/20/2003 03:00:00,Merchant Hold,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::HOLD, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @param string $expectedResponse
     * @param int $code
     *
     * @return \Orkestra\Transactor\Transactor\PaymentsXpress\AchTransactor
     */
    protected function getTransactor($expectedResponse, $code = 200)
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
        $account->setAccountNumber('123456789');
        $account->setRoutingNumber('123123123');
        $account->setAccountType(new BankAccount\AccountType(BankAccount\AccountType::PERSONAL_CHECKING));

        $credentials = new Credentials();
        $credentials->setCredential('providerId', '1000');
        $credentials->setCredential('providerGateId', 'gateid');
        $credentials->setCredential('providerGateKey', 'gatekey');
        $credentials->setCredential('merchantId', '2000');
        $credentials->setCredential('merchantGateId', 'test');
        $credentials->setCredential('merchantGateKey', 'test');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }
}
