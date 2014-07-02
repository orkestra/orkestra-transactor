<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Transactor\AuthorizeNet;

use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Account\BankAccount\AccountType;
use Orkestra\Transactor\Serializer\AuthorizeNet\Card\TransactionNormalizer;
use Orkestra\Transactor\Transactor\AuthorizeNet\AchTransactor;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Result;
use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

/**
 * Unit tests for the Auth net Ach Transactor
 *
 * @group orkestra
 * @group transactor
 */
class AchTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = $this->getTransactor();

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
        $transactor = $this->getTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::SALE)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::AUTH)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::CAPTURE)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::CREDIT)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::REFUND)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::VOID)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::QUERY)));
        // $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::UPDATE)));
    }

    public function testSaleSuccess()
    {
        $transactor = $this->getTransactor('<?xml version="1.0" encoding="utf-8"?><createTransactionResponse><refId>123456</refId><messages><resultCode>Ok</resultCode><message><code>I00001</code><text>Successful.</text></message></messages><transactionResponse><responseCode>1</responseCode><authCode/><avsResultCode>P</avsResultCode><cvvResultCode/><cavvResultCode/><transId>2214627492</transId><refTransID/><transHash>7A6DCD2645DF873C035DCE4832C08036</transHash><testRequest>0</testRequest><accountNumber>XXXX5678</accountNumber><accountType>eCheck</accountType><messages><message><code>1</code><description>This transaction has been approved.</description></message></messages></transactionResponse></createTransactionResponse>');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);
        $request = $result->getData('request');

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('2214627492', $result->getExternalId());
        $this->assertInternalType('array', $request);
        $this->assertArrayHasKey('bankAccount', $request['transactionRequest']['payment']);
        $this->assertEquals('[filtered]', $request['transactionRequest']['payment']['bankAccount']);
    }

    public function testSaleError()
    {
        $transactor = $this->getTransactor('<?xml version="1.0" encoding="utf-8"?><createTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><refId>123456</refId><messages><resultCode>Error</resultCode><message><code>E00027</code><text>The transaction was unsuccessful.</text></message></messages><transactionResponse><responseCode>3</responseCode><authCode /><avsResultCode>P</avsResultCode><cvvResultCode /><cavvResultCode /><transId>0</transId><refTransID /><transHash>D3038FEB2269EBFA356990B8FFDA60C5</transHash><testRequest>0</testRequest><accountNumber>XXXX5678</accountNumber><accountType>eCheck</accountType><errors><error><errorCode>9</errorCode><errorText>The ABA code is invalid</errorText></error></errors></transactionResponse></createTransactionResponse>');
        $transaction = $this->getTransaction();
        $transaction->setAmount(.5);

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('Error Code: 9. The ABA code is invalid', $result->getMessage());
        $this->assertEquals('', $result->getExternalId());
    }

    public function testSaleDecline()
    {
        $transactor = $this->getTransactor('<?xml version="1.0" encoding="utf-8"?><createTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><refId>123456</refId><messages><resultCode>Error</resultCode><message><code>E00027</code><text>The transaction was unsuccessful.</text></message></messages><transactionResponse><responseCode>3</responseCode><authCode /><avsResultCode>P</avsResultCode><cvvResultCode /><cavvResultCode /><transId>0</transId><refTransID /><transHash>D700EA654428B5961BC301497971D789</transHash><testRequest>0</testRequest><accountNumber>XXXX5678</accountNumber><accountType>eCheck</accountType><errors><error><errorCode>49</errorCode><errorText>The transaction amount submitted was greater than the maximum amount allowed.</errorText></error></errors></transactionResponse></createTransactionResponse>');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('Error Code: 49. The transaction amount submitted was greater than the maximum amount allowed.', $result->getMessage());
        $this->assertEquals('', $result->getExternalId());
    }

    public function testHttpError()
    {
        $transactor = $this->getTransactor('', 503);
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('Error Code: 200. An error occurred while processing the payment. Please try again.', $result->getMessage());
        $this->assertEmpty($result->getExternalId());
    }


    protected function getTransactor($expectedResponse = '', $code = 200)
    {
        $client = new Client();
        $normalizer = new TransactionNormalizer();
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response($code, null, $expectedResponse));
        $client->addSubscriber($plugin);

        return new AchTransactor($client, $normalizer);
    }

    /**
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    protected function getTransaction()
    {
        $account = new BankAccount();
        $account->setAccountNumber('123123123');
        $account->setRoutingNumber('123123123');
        $account->setAccountType(new AccountType(AccountType::PERSONAL_CHECKING));

        $credentials = new Credentials();
        $credentials->setCredential('username', 'demo');
        $credentials->setCredential('password', 'password');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new NetworkType(NetworkType::ACH));
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }

}
