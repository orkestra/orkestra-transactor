<?php

namespace Orkestra\Transactor\Entities\Transactor;

use Orkestra\Transactor\Entities\TransactorBase,
    Orkestra\Transactor\Entities\Transaction,
    Orkestra\Transactor\Kernel\HttpKernel,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class NmiTransactor extends TransactorBase
{
    protected static $_supportedTypes = array(
        Transaction::TYPE_CARD_SALE
    );
    
    public function transact(Transaction $transaction)
    {
        parent::transact($transaction);
        
        $request = new Request();
        
        // Set appropriate request information
        
        $kernel = new HttpKernel();
        
        $response = $kernel->handle($request);
        
        // Parse the response information to determine the result
    }
}