<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests;

use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Result;

/**
 * Tests the functionality provided by the AbstractTransactor
 *
 * @group orkestra
 * @group transactor
 */
class AbstractTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testTransactorSupportsNetwork()
    {
        $transactor = new TestTransactor();

        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
    }

    public function testTransactorTransactUnsupportedNetwork()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'Transaction network "ACH" is not supported by this Transactor');

        $transactor->transact($transaction);
    }

    public function testTransactorTransactProcessedTransaction()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->getResult()->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'This transaction has already been processed');

        $transactor->transact($transaction);
    }

    public function testTransactorSupportsType()
    {
        $transactor = new TestTransactor();

        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::SALE)));
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::AUTH)));
    }

    public function testTransactorTransactUnsupportedType()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::AUTH));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'Transaction type "Auth" is not supported by this Transactor');

        $transactor->transact($transaction);
    }

    public function testTransactorCatchesExceptions()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD));

        $result = $transactor->transact($transaction);

        $this->assertEquals(Result\ResultStatus::ERROR, $result->getStatus());
        $this->assertEquals('An internal error occurred while processing the transaction.', $result->getMessage());
        $this->assertEquals('Critical error', $result->getData('message'));
        $this->assertNotEmpty($result->getData('trace'));
        $this->assertNotEmpty($result->getTransactor());
    }
}

class TestTransactor extends AbstractTransactor
{
    protected static $_supportedNetworks = array(
        Transaction\NetworkType::CARD
    );

    protected static $_supportedTypes = array(
        Transaction\TransactionType::SALE
    );

    protected function _doTransact(Transaction $transaction, $options = array())
    {
        throw new \RuntimeException('Critical error');
    }

    function getName()
    {
        return 'Test Transactor';
    }

    public function getType()
    {
        return 'orkestra.test_transactor';
    }
}
