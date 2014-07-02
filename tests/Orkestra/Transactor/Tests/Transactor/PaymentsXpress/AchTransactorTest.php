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
use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Account\BankAccount\AccountType;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;
use Orkestra\Transactor\Transactor\PaymentsXpress\AchTransactor;

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
        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::ACH)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::SWIPED)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CASH)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::POINTS)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CHECK)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new AchTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::SALE)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::AUTH)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::CAPTURE)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::CREDIT)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::REFUND)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::VOID)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::QUERY)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::UPDATE)));
    }

    public function testResponseError()
    {
        $transactor = $this->getTransactor('', 503);
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Client Error: An error occurred while contacting the PaymentsXpress system', $result->getMessage());
    }

    public function testSaleSuccess()
    {
        $transactor = $this->getTransactor('{"CommandStatus":"Approved","Description":"","ErrorInformation":"","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"56789","ResponseCode":"100"}');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);
        $request = $result->getData('request');

        $this->assertEquals(ResultStatus::PENDING, $result->getStatus()->getValue());
        $this->assertEquals('56789', $result->getExternalId());

        $this->assertInternalType('array', $request);
        $this->assertArrayHasKey('AccountNumber', $request);
        $this->assertEquals('[filtered]', $request['AccountNumber']);
        $this->assertArrayHasKey('RoutingNumber', $request);
        $this->assertEquals('[filtered]', $request['RoutingNumber']);

        return $transaction;
    }

    public function testSaleError()
    {
        $transactor = $this->getTransactor('{"CommandStatus":"Error","Description":"Internal Gateway Error","ErrorInformation":"","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"54321","ResponseCode":"100"}');
        $transaction = $this->getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Internal Gateway Error', $result->getMessage());
        $this->assertEquals('54321', $result->getExternalId());
    }

    public function testSaleDecline()
    {
        $transactor = $this->getTransactor('{"CommandStatus":"Declined","Description":"Invalid Gateway Credentials","ErrorInformation":"Provider_Credentials","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"12345","ResponseCode":"100"}');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('Invalid Gateway Credentials: Provider_Credentials', $result->getMessage());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithNoUpdateSetsParentStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertNotSame($result->getStatus(), $parent->getResult()->getStatus());
        $this->assertEquals(ResultStatus::PENDING, $result->getStatus()->getValue());
        $this->assertEquals($result->getStatus()->getValue(), $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithScheduledStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('12345,,,Cancelled,02/20/2003 02:59:00,Cancelled,,,,
56789,,,Created,02/20/2003 03:00:00,Scheduled,,,,
45666,,,Cancelled,02/20/2003 03:01:00,Cancelled,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::PENDING, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithCancelledStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('56789,,,Cancelled,02/20/2003 03:00:00,Cancelled,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::CANCELLED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithProcessedStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('56789,,,Submitted,02/20/2003 03:00:00,In-Process,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::PROCESSED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithApprovedStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('56789,,,Cleared,02/20/2003 03:00:00,Cleared,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithDeclinedStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('56789,,,Rejected,02/20/2003 03:00:00,Failed Verification,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithChargedBackStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('56789,,,Charged Back,02/20/2003 03:00:00,Charged Back,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::CHARGED_BACK, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithHoldBackStatus(TransactionInterface $parent)
    {
        $transactor = $this->getTransactor('56789,,,Held by Merchant,02/20/2003 03:00:00,Merchant Hold,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,');
        $transaction = $parent->createChild(new TransactionType(TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::HOLD, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @param string $expectedResponse
     * @param int    $code
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
     * @return TransactionInterface
     */
    protected function getTransaction()
    {
        $account = new BankAccount();
        $account->setAccountNumber('123456789');
        $account->setRoutingNumber('123123123');
        $account->setAccountType(new AccountType(AccountType::PERSONAL_CHECKING));

        $credentials = new Credentials();
        $credentials->setCredential('providerId', '1000');
        $credentials->setCredential('providerGateId', 'gateid');
        $credentials->setCredential('providerGateKey', 'gatekey');
        $credentials->setCredential('merchantId', '2000');
        $credentials->setCredential('merchantGateId', 'test');
        $credentials->setCredential('merchantGateKey', 'test');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new NetworkType(NetworkType::ACH));
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }
}
