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

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\Result;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Serializer\AuthorizeNet\Card\TransactionNormalizer;
use Orkestra\Transactor\Transactor\AuthorizeNet\CardTransactor;
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;
use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

/**
 * Unit tests for the AuthNet Card Transactor
 *
 * @group orkestra
 * @group transactor
 */
class CardTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = $this->getTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::CARD)));
        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::SWIPED)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::ACH)));
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
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::AUTH)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::CAPTURE)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::REFUND)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::VOID)));

        // Unsupported
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::QUERY)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::UPDATE)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::CREDIT)));
    }

    public function testCardSaleSuccess()
    {
        $transactor = $this->getTransactor('<?xml version="1.0" encoding="utf-8"?><createTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><refId>123456</refId><messages><resultCode>Ok</resultCode><message><code>I00001</code><text>Successful.</text></message></messages><transactionResponse><responseCode>1</responseCode><authCode>UGELQC</authCode><avsResultCode>E</avsResultCode><cavvResultCode /><transId>2148061808</transId><refTransID /><transHash>0B428D8A928AAC61121AF2F6EAC5FF3F</transHash><testRequest>0</testRequest><accountNumber>XXXX0012</accountNumber><accountType>DiscoverCard</accountType><message><code>1</code><description>This transaction has been approved.</description></message></transactionResponse></createTransactionResponse>');
        $transaction = $this->getTransaction();

        $result = $transactor->transact($transaction);
        $request = $result->getData('request');

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('2148061808', $result->getExternalId());
        $this->assertEquals('[filtered]', $request['transactionRequest']['payment']['creditCard']);
    }

    public function testCardSaleDecline()
    {
        $transactor = $this->getTransactor('<?xml version="1.0" encoding="utf-8"?><createTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><refId>123456</refId><messages><resultCode>Error</resultCode><message><code>E00027</code><text>The transaction was unsuccessful.</text></message></messages><transactionResponse><responseCode>3</responseCode><authCode /><avsResultCode>P</avsResultCode><cvvResultCode /><cavvResultCode /><transId>0</transId><refTransID /><transHash>D3038FEB2269EBFA356990B8FFDA60C5</transHash><testRequest>0</testRequest><accountNumber>XXXX0015</accountNumber><accountType /><errors><error><errorCode>6</errorCode><errorText>The credit card number is invalid.</errorText></error></errors></transactionResponse></createTransactionResponse>');
        $transaction = $this->getTransaction();
        $transaction->getAccount()->setAccountNumber('00000000015');

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::DECLINED, $result->getStatus()->getValue());
        $this->assertEquals('Error Code: 6. The credit card number is invalid.', $result->getMessage());
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

    public function testNonSwipeOnSwipeNetworkValidation()
    {
        $transaction = $this->getTransaction();
        $transaction->setNetwork(new NetworkType(NetworkType::SWIPED));

        $transactor = $this->getTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus()->getValue());
        $this->assertEquals('An internal error occurred while processing the transaction.', $result->getMessage());
        $this->assertEmpty($result->getExternalId());
        $this->assertEquals('Validation failed: invalid account type: Credit Card', $result->getData('message'));
    }

    public function testSwipedSalesSuccess()
    {
        $transactor = $this->getTransactor('<?xml version="1.0" encoding="utf-8"?><createTransactionResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><refId>123456</refId><messages><resultCode>Ok</resultCode><message><code>I00001</code><text>Successful.</text></message></messages><transactionResponse><responseCode>1</responseCode><authCode>9LASWF</authCode><avsResultCode>Y</avsResultCode><cvvResultCode /><transId>2231753443</transId><refTransID /><transHash>023F9A51FC1E84A553295ED0254E429E</transHash><testRequest>0</testRequest><accountNumber>XXXX1111</accountNumber><accountType>Visa</accountType><messages><message><code>1</code><description>This transaction has been approved.</description></message></messages></transactionResponse></createTransactionResponse>');
        $transaction = $this->getSwipedTransaction();

        $result = $transactor->transact($transaction);
        $request = $result->getData('request');

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals('2231753443', $result->getExternalId());
        $this->assertInternalType('array', $request['transactionRequest']['payment']);
        $this->assertEquals('[filtered]', $request['transactionRequest']['payment']['trackData']);
    }

    protected function getTransactor($expectedResponse = '', $code = 200)
    {
        $client = new Client();
        $normalizer = new TransactionNormalizer();
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response($code, null, $expectedResponse));
        $client->addSubscriber($plugin);

        return new CardTransactor($client, $normalizer);
    }

    /**
     * @param int $expMonth
     * @param int $expYear
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    protected function getTransaction($expMonth = 10,$expYear = 2010)
    {
        $account = new CardAccount();
        $account->setAccountNumber('6011000000000012');
        $account->setExpMonth(new Month($expMonth));
        $account->setExpYear(new Year($expYear));

        $credentials = new Credentials();
        $credentials->setCredential('username', 'demo');
        $credentials->setCredential('password', 'password');

        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setNetwork(new NetworkType(NetworkType::CARD));
        $transaction->setType(new TransactionType(TransactionType::SALE));
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
        $transaction->setNetwork(new NetworkType(NetworkType::SWIPED));
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setCredentials($credentials);
        $transaction->setAccount($account);

        return $transaction;
    }
}
