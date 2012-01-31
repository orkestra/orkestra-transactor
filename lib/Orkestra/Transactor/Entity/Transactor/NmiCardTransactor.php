<?php

namespace Orkestra\Transactor\Entity\Transactor;

use Doctrine\ORM\Mapping as ORM,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;
    
use Orkestra\Transactor\Entity\TransactorBase,
    Orkestra\Transactor\Entity\Transaction,
    Orkestra\Transactor\Entity\Account\CardAccount,
    Orkestra\Transactor\Kernel\HttpKernel,
    Orkestra\Transactor\Exception\ValidationException;

/**
 * NMI Transactor
 *
 * Concrete NMI Transactor implementation
 *
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
class NmiCardTransactor extends TransactorBase
{
    protected static $_supportedTypes = array(
        Transaction::TYPE_CARD_SALE
    );
    
    public function transact(Transaction $transaction, $options = array())
    {
        parent::transact($transaction);
        
        $this->_validateTransaction($transaction);
        
        $account = $transaction->getAccount();
                
        // Set appropriate request information
        $params = array(
            'type' => $this->_getNmiType($transaction),
            'username' => $this->getCredential('username'),
            'password' => $this->getCredential('password'),
            'ccnumber' => $account->getAccountNumber(),
            'ccexp' => $account->getExpMonth() . substr($account->getExpYear(), 0, 2),
            'amount' => $transaction->getAmount(),
        );
        
        $request = Request::create('https://secure.networkmerchants.com/api/transact.php', 'POST', $params);
        
        $kernel = new HttpKernel();
        
        $response = $kernel->handle($request);
        
        // Parse the response information to determine the result
    }
    
    public function getType()
    {
        return 'NMI Card Transactor';
    }
    
    protected function _validateTransaction(Transaction $transaction)
    {
        $account = $transaction->getAccount();
        
        if (empty($account) || !$account instanceof CardAccount) {
            throw ValidationException::missingAccountInformation();
        }
        
        if (null === $account->getAccountNumber()) {
            throw ValidationException::missingRequiredParameter('account number');
        }
        else if (null === $account->getExpMonth() || null === $account->getExpYear()) {
            throw ValidationException::missingRequiredParameter('card expiration');
        }
    }
    
    protected function _getNmiType(Transaction $transaction)
    {
        switch ($transaction->getType()) {
            case Transaction::TYPE_CARD_SALE:
                return 'sale';
            case Transaction::TYPE_CARD_AUTH:
                return 'auth';
            default:
                return 'nope';
        }
    }
}