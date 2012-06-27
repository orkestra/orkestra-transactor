<?php

namespace Orkestra\Transactor\Tests\Transactor\NetworkMerchants;

use Orkestra\Common\Kernel\HttpKernel;
use Symfony\Component\HttpFoundation\Response;

use Orkestra\Transactor\Transactor\NetworkMerchants\CardTransactor;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

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
        $transactor = $this->_getTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = $this->_getTransactor();

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
        $response = new Response(
            'response=1&responsetext=SUCCESS&authcode=123456&transactionid=12345&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=100',
            200
        );

        $kernel = $this->_getMockKernel();
        $kernel->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($response));

        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('12345', $result->getExternalId());
    }

    public function testCardSaleError()
    {
        $response = new Response(
            'response=3&responsetext=Invalid Credit Card Number REFID:330352367&authcode=&transactionid=&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=300',
            200
        );

        $kernel = $this->_getMockKernel();
        $kernel->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($response));

        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Invalid Credit Card Number REFID:330352367', $result->getMessage());
        $this->assertEquals('', $result->getExternalId());
    }

    public function testCardSaleDecline()
    {
        $response = new Response(
            'response=2&responsetext=DECLINE&authcode=&transactionid=54321&avsresponse=&cvvresponse=&orderid=&type=sale&response_code=200',
            200
        );

        $kernel = $this->_getMockKernel();
        $kernel->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($response));

        $transactor = $this->_getTransactor($kernel);
        $transaction = $this->_getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('DECLINE', $result->getMessage());
        $this->assertEquals('54321', $result->getExternalId());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Orkestra\Common\Kernel\HttpKernel
     */
    protected function _getMockKernel()
    {
        $mockKernel = $this->getMock('Orkestra\Common\Kernel\HttpKernel');

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

        $transactor = new CardTransactor($kernel);

        return $transactor;
    }

    /**
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    protected function _getTransaction()
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
}
