<?php

namespace Orkestra\Transactor\Tests;

require_once __DIR__ . '/../../../bootstrap.php';

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
    public function testTransactorSupportsTrue()
    {
        $transactor = new TestTransactor();

        $this->assertTrue($transactor->supports(new Transaction\TransactionType(Transaction\TransactionType::CARD_SALE)));
    }

    public function testTransactorSupportsFalse()
    {
        $transactor = new TestTransactor();

        $this->assertFalse($transactor->supports(new Transaction\TransactionType(Transaction\TransactionType::CARD_AUTH)));
    }

    public function testTransactorTransactUnsupportedType()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::CARD_AUTH));

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'Transaction type "Card Auth" is not supported by this Transactor');

        $transactor->transact($transaction);
    }

    public function testTransactorTransactProcessedTransaction()
    {
        $transactor = new TestTransactor();

        $transaction = new Transaction();
        $result = new Result($transactor, $transaction);

        $this->setExpectedException('Orkestra\Transactor\Exception\TransactorException', 'This transaction has already been processed');

        $transactor->transact($transaction);
    }
}

class TestTransactor extends AbstractTransactor
{
    protected static $_supportedTypes = array(
        Transaction\TransactionType::CARD_SALE
    );

    protected function _doTransact(Transaction $transaction, $options = array())
    {

    }

    function getName()
    {
        return 'Test Transactor';
    }

    public function getType()
    {
        return 'orkestra.test_transaction';
    }
}
