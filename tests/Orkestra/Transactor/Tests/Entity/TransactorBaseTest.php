<?php

namespace Orkestra\Transactor\Tests\Entity;

use Orkestra\Transactor\Entity\TransactionType,
    Orkestra\Transactor\Entity\Transaction,
    Orkestra\Transactor\Entity\TransactionResult\ApprovedResult;

/**
 * TransactorBase Test
 *
 * Tests the functionality provided by the TransactorBase
 */
class TransactorBaseTest extends \PHPUnit_Framework_TestCase
{
    public function testTransactorSupportsTrue()
    {
        $transactor = new TestTransactor();
        
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardSale)));
    }
    
    public function testTransactorSupportsFalse()
    {
        $transactor = new TestTransactor();
        
        $this->assertFalse($transactor->supports(new TransactionType(TransactionType::CardAuth)));
    }
    
    public function testTransactorTransactUnsupportedType()
    {
        $transactor = new TestTransactor();
        
        $transaction = new Transaction();
        $transaction->setType(new TransactionType(TransactionType::CardAuth));
        
        $this->setExpectedException('Orkestra\Transactor\Exception\TransactException', 'Transaction type "card.auth" is not supported by this Transactor');
        
        $transactor->transact($transaction);
    }
    
    public function testTransactorTransactProcessedTransaction()
    {
        $transactor = new TestTransactor();
        
        $transaction = new Transaction();
        $result = new ApprovedResult($transactor, $transaction);
        
        $this->setExpectedException('Orkestra\Transactor\Exception\TransactException', 'This transaction has already been processed');
        
        $transactor->transact($transaction);
    }
}

class TestTransactor extends \Orkestra\Transactor\Entity\TransactorBase
{
    protected static $_supportedTypes = array(
        TransactionType::CardSale
    );
    
    public function getType()
    {
        return 'Test Transactor';
    }
}