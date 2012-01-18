<?php

namespace Orkestra\Transactor\Tests\Entities;

class TransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testTransactorSupportsTrue()
    {
        $transactor = new TestTransactor();
        
        $this->assertTrue($transactor->supports(\Orkestra\Transactor\Entity\Transaction::TYPE_CARD_SALE));
    }
    
    public function testTransactorSupportsFalse()
    {
        $transactor = new TestTransactor();
        
        $this->assertFalse($transactor->supports(\Orkestra\Transactor\Entity\Transaction::TYPE_CARD_AUTH));
    }
    
    public function testTransactorSupportsInvalidType()
    {
        $transactor = new TestTransactor();
        
        $this->setExpectedException('InvalidArgumentException');
        
        $transactor->supports('invalid.type');
    }
    
    public function testTransactorTransactUnsupportedType()
    {
        $transactor = new TestTransactor();
        
        $transaction = new \Orkestra\Transactor\Entity\Transaction();
        $transaction->setType(\Orkestra\Transactor\Entity\Transaction::TYPE_CARD_AUTH);
        $transaction->setTransacted(true);
        
        $this->setExpectedException('Orkestra\Transactor\Exception\TransactException');
        
        $transactor->transact($transaction);
    }
    
    public function testTransactorTransactProcessedTransaction()
    {
        $transactor = new TestTransactor();
        
        $transaction = new \Orkestra\Transactor\Entity\Transaction();
        $transaction->setTransacted(true);
        
        $this->setExpectedException('Orkestra\Transactor\Exception\TransactException');
        
        $transactor->transact($transaction);
    }
}

class TestTransactor extends \Orkestra\Transactor\Entity\TransactorBase
{
    protected static $_supportedTypes = array(
        \Orkestra\Transactor\Entity\Transaction::TYPE_CARD_SALE
    );
}