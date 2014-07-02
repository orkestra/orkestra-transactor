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

use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::MFA)));
    }

    public function testTransactorTransactUnsupportedNetwork()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setNetwork(new NetworkType(NetworkType::ACH));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'Transaction network "ACH" is not supported by this Transactor');

        $transactor->transact($transaction);
    }

    public function testTransactorTransactProcessedTransaction()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->getResult()->setStatus(new ResultStatus(ResultStatus::APPROVED));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'This transaction has already been processed');

        $transactor->transact($transaction);
    }

    public function testTransactorSupportsType()
    {
        $transactor = new TestTransactor();

        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::SALE)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::AUTH)));
    }

    public function testTransactorTransactUnsupportedType()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new TransactionType(TransactionType::AUTH));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'Transaction type "Auth" is not supported by this Transactor');

        $transactor->transact($transaction);
    }

    public function testTransactorCatchesExceptions()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setNetwork(new NetworkType(NetworkType::CARD));

        $result = $transactor->transact($transaction);

        $this->assertEquals(ResultStatus::ERROR, $result->getStatus());
        $this->assertEquals('An internal error occurred while processing the transaction.', $result->getMessage());
        $this->assertEquals('Critical error', $result->getData('message'));
        $this->assertNotEmpty($result->getData('trace'));
        $this->assertNotEmpty($result->getTransactor());
    }

    public function testOptionsResolverExceptionIsCaught()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setNetwork(new NetworkType(NetworkType::CARD));

        $result = $transactor->transact($transaction, array('test' => 'invalid value'));
        $this->assertEquals(ResultStatus::ERROR, $result->getStatus());
        $this->assertEquals('An internal error occurred while processing the transaction.', $result->getMessage());
        $this->assertContains('value "invalid value"', $result->getData('message'));
        $this->assertNotEmpty($result->getData('trace'));
        $this->assertNotEmpty($result->getTransactor());
    }
}

class TestTransactor extends AbstractTransactor
{
    protected static $supportedNetworks = array(
        NetworkType::CARD
    );

    protected static $supportedTypes = array(
        TransactionType::SALE
    );

    protected function doTransact(TransactionInterface $transaction, array $options = array())
    {
        throw new \RuntimeException('Critical error');
    }

    protected function configureResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'test' => 'value'
        ));
        
        $resolver->setAllowedValues('test', array('value'));
    }

    public function getName()
    {
        return 'Test Transactor';
    }

    public function getType()
    {
        return 'orkestra.test_transactor';
    }
}
