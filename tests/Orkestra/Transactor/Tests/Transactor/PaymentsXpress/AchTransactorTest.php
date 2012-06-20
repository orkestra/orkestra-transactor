<?php

namespace Orkestra\Transactor\Tests\Transactor\PaymentsXpress;

require_once __DIR__ . '/../../../../../bootstrap.php';

use Orkestra\Common\Kernel\HttpKernel;
use Symfony\Component\HttpFoundation\Response;

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
        $transactor = $this->_getTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = $this->_getTransactor();

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
        $response = new Response(
            '',
            500
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::ERROR, $result->getType()->getValue());
        $this->assertEquals('An error occurred while contacting the PaymentsXpress system', $result->getMessage());
    }

    public function testSaleSuccess()
    {
        $response = new Response(
            '{"CommandStatus":"Approved","Description":"","ErrorInformation":"","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"56789","ResponseCode":"100"}',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::PENDING, $result->getType()->getValue());
        $this->assertEquals('56789', $result->getExternalId());

        return $transaction;
    }

    public function testSaleError()
    {
        $response = new Response(
            '{"CommandStatus":"Error","Description":"Internal Gateway Error","ErrorInformation":"","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"54321","ResponseCode":"100"}',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::ERROR, $result->getType()->getValue());
        $this->assertEquals('Internal Gateway Error', $result->getMessage());
        $this->assertEquals('54321', $result->getExternalId());
    }

    public function testSaleDecline()
    {
        $response = new Response(
            '{"CommandStatus":"Declined","Description":"Invalid Gateway Credentials","ErrorInformation":"Provider_Credentials","ExpressVerify":{"Status":null,"Code":null,"Description":null},"AVS":{"Description":null,"Code":null},"CVN":{"Description":null,"Code":null},"ResponseData":null,"PaymentKey":null,"AuthorizationCode":null,"Provider_TransactionID":null,"TransAct_ReferenceID":"12345","ResponseCode":"100"}',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::DECLINED, $result->getType()->getValue());
        $this->assertEquals('Invalid Gateway Credentials: Provider_Credentials', $result->getMessage());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithNoUpdateSetsParentStatus(Transaction $parent)
    {
        $response = new Response(
            'Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertNotSame($result->getType(), $parent->getResult()->getType());
        $this->assertEquals(Result\ResultType::PENDING, $result->getType()->getValue());
        $this->assertEquals($result->getType()->getValue(), $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithScheduledStatus(Transaction $parent)
    {
        $response = new Response(
            '12345,,,Cancelled,02/20/2003 02:59:00,Cancelled,,,,
56789,,,Created,02/20/2003 03:00:00,Scheduled,,,,
45666,,,Cancelled,02/20/2003 03:01:00,Cancelled,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::PENDING, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithCancelledStatus(Transaction $parent)
    {
        $response = new Response(
            '56789,,,Cancelled,02/20/2003 03:00:00,Cancelled,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::CANCELLED, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithProcessedStatus(Transaction $parent)
    {
        $response = new Response(
            '56789,,,Submitted,02/20/2003 03:00:00,In-Process,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::PROCESSED, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithApprovedStatus(Transaction $parent)
    {
        $response = new Response(
            '56789,,,Cleared,02/20/2003 03:00:00,Cleared,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::APPROVED, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithDeclinedStatus(Transaction $parent)
    {
        $response = new Response(
            '56789,,,Rejected,02/20/2003 03:00:00,Failed Verification,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::DECLINED, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithChargedBackStatus(Transaction $parent)
    {
        $response = new Response(
            '56789,,,Charged Back,02/20/2003 03:00:00,Charged Back,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::CHARGED_BACK, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @depends testSaleSuccess
     */
    public function testQueryWithHoldBackStatus(Transaction $parent)
    {
        $response = new Response(
            '56789,,,Held by Merchant,02/20/2003 03:00:00,Merchant Hold,,,,
Command Response,Approved,000,Command Successful. Approved.,,12345,,,',
            200
        );

        $kernel = $this->_getMockKernel($response);
        $transactor = $this->_getTransactor($kernel);
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::QUERY));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultType::HOLD, $result->getType()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Orkestra\Common\Kernel\HttpKernel
     */
    protected function _getMockKernel($response = null)
    {
        $mockKernel = $this->getMock('Orkestra\Common\Kernel\HttpKernel');
        if ($response) {
            $mockKernel->expects($this->once())
                ->method('handle')
                ->will($this->returnValue($response));
        }

        return $mockKernel;
    }

    /**
     * @return \Orkestra\Transactor\Transactor\NetworkMerchants\CardTransactor
     */
    protected function _getTransactor($kernel = null)
    {
        if (!$kernel) {
            $kernel = new HttpKernel();
        }

        $transactor = new AchTransactor($kernel);

        return $transactor;
    }

    /**
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    protected function _getTransaction()
    {
        $account = new BankAccount();
        $account->setAccountNumber('123456789');
        $account->setRoutingNumber('123123123');
        $account->setAccountType(new BankAccount\AccountType(BankAccount\AccountType::PERSONAL_CHECKING));

        $credentials = new Credentials();
        $credentials->setCredential('providerId', '1074');
        $credentials->setCredential('providerGateId', 'sp-57181796');
        $credentials->setCredential('providerGateKey', 'p8u-6wb*');
        $credentials->setCredential('merchantId', '2001');
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
