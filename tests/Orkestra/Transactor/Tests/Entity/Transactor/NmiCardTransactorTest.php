<?php

namespace Orkestra\Transactor\Tests\Entity\Transactor;

use Orkestra\Transactor\Entity\Transactor\NmiCardTransactor,
    Orkestra\Transactor\Entity\TransactionType,
    Orkestra\Transactor\Entity\Transaction,
    Orkestra\Transactor\Entity\TransactionResult\ApprovedResult,
    Orkestra\Transactor\Entity\Account\CardAccount,
    Orkestra\Transactor\Type\Month,
    Orkestra\Transactor\Type\Year;
    
use Orkestra\Common\Kernel\HttpKernel;

/**
 * NmiTransactor Test
 *
 * Tests the functionality provided by the NMI Transactor.
 *
 * NOTE: This sends live web requests and some tests will therefore fail if 
 * no internet connection is available
 *
 * @group wired
 */
class NmiCardTransactorTest extends \PHPUnit_Framework_TestCase
{
    protected function _getBrokenDemoTransactor()
    {
        $transactor = new NmiCardTransactor();
        $transactor->setCredentials(array('username' => 'demo', 'password' => 'password'));
        
        return $transactor;
    }
    
    protected function _getDemoTransactor()
    {
        $transactor = $this->_getBrokenDemoTransactor();
        
        $kernel = new HttpKernel();
        
        $container = $this->getMock('Symfony\Component\DependencyInjection\Container');
        $container->expects($this->once())
                  ->method('get')
                  ->will($this->returnValue($kernel));
        
        $transactor->setContainer($container);
        
        return $transactor;
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new NmiCardTransactor();
        
        // Supported
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardSale)));
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardAuth)));
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardCapture)));
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardCredit)));
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardRefund)));
        $this->assertTrue($transactor->supports(new TransactionType(TransactionType::CardVoid)));
        
        // Unsupported
        $this->assertFalse($transactor->supports(new TransactionType(TransactionType::AchRequest)));
        $this->assertFalse($transactor->supports(new TransactionType(TransactionType::AchResponse)));
        $this->assertFalse($transactor->supports(new TransactionType(TransactionType::MfaTransfer)));
    }
    
    public function testGetTypeReturnsProperValue()
    {
        $transactor = new NmiCardTransactor();
        
        $this->assertEquals('NMI Card Transactor', $transactor->getType());
    }
    
    public function testRequiresDIContainer()
    {
        $transactor = $this->_getBrokenDemoTransactor();
        
        $account = new CardAccount();
        $account->setAccountNumber('4111111111111111');
        $account->setExpMonth(new Month(10));
        $account->setExpYear(new Year(2010));
        
        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setType(new TransactionType(TransactionType::CardSale));
        $transaction->setAccount($account);
        
        $this->setExpectedException('RuntimeException', 'NMI Card Transactor requires an instance of Symfony\Component\DependencyInjection\ContainerInterface to transact');
        
        $transactor->transact($transaction);
    }
    
    public function testCardSale()
    {
        $transactor = $this->_getDemoTransactor();
        
        $account = new CardAccount();
        $account->setAccountNumber('4111111111111111');
        $account->setExpMonth(new Month(10));
        $account->setExpYear(new Year(2010));
        
        $transaction = new Transaction();
        $transaction->setAmount(10);
        $transaction->setType(new TransactionType(TransactionType::CardSale));
        $transaction->setAccount($account);
        
        $result = $transactor->transact($transaction);
        
        $this->assertInstanceOf('Orkestra\Transactor\Entity\TransactionResult\ApprovedResult', $result);
    }
}