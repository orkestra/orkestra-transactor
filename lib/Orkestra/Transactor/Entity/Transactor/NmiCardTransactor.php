<?php

namespace Orkestra\Transactor\Entity\Transactor;

use Doctrine\ORM\Mapping as ORM,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use Orkestra\Common\Kernel\HttpKernel;

use Orkestra\Transactor\Entity\TransactorBase,
    Orkestra\Transactor\Entity\Transaction,
    Orkestra\Transactor\Entity\TransactionType,
    Orkestra\Transactor\Entity\TransactionResult\ApprovedResult,
    Orkestra\Transactor\Entity\TransactionResult\DeclinedResult,
    Orkestra\Transactor\Entity\TransactionResult\ErrorResult,
    Orkestra\Transactor\Entity\Account\CardAccount,
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
        TransactionType::CardSale,
        TransactionType::CardAuth,
        TransactionType::CardCapture,
        TransactionType::CardCredit,
        TransactionType::CardRefund,
        TransactionType::CardVoid,
    );
    
    /**
     * {@inheritdoc}
     */
    public function transact(Transaction $transaction, $options = array())
    {
        parent::transact($transaction);
        
        if (empty($this->_container)) {
            throw new \RuntimeException(sprintf('%s requires an instance of Symfony\Component\DependencyInjection\ContainerInterface to transact', $this->getType()));
        }

        $this->_validateTransaction($transaction);
        $params = $this->_buildParams($transaction);

        $request = Request::create('https://secure.networkmerchants.com/api/transact.php', 'POST', $params);
        $kernel = $this->_container->get('orkestra.kernel.http');
        $response = $kernel->handle($request);
        
        $responseData = array();

        parse_str($response->getContent(), $responseData);
                
        if (empty($responseData['response']) || $responseData['response'] == '3') {
            $result = new ErrorResult($this, $transaction, empty($responseData['transactionid']) ? '' : $responseData['transactionid'],
                empty($responseData['responsetext']) ? 'An unknown error occurred.' : $responseData['responsetext']);
        }
        else if ($responseData['response'] == '2') {
            $result = new DeclinedResult($this, $transaction, $responseData['transactionid'],
                empty($responseData['responsetext']) ? 'An unknown error occurred.' : $responseData['responsetext']);
        }
        else {
            $result = new ApprovedResult($this, $transaction, $responseData['transactionid']);
        }
        
        $result->setData('request', $params);
        $result->setData('response', $responseData);
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'NMI Card Transactor';
    }
    
    protected function _validateTransaction(Transaction $transaction)
    {
        $account = $transaction->getAccount();
        
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(TransactionType::CardCapture, TransactionType::CardRefund, TransactionType::CardVoid))) {
            throw ValidationException::parentTransactionRequired();
        }
        
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
        switch ($transaction->getType()->getValue()) {
            case TransactionType::CardSale:
                return 'sale';
            case TransactionType::CardAuth:
                return 'auth';
            case TransactionType::CardCapture:
                return 'capture';
            case TransactionType::CardCredit:
                return 'credit';
            case TransactionType::CardRefund:
                return 'refund';
            case TransactionType::CardVoid:
                return 'void';
        }
    }
    
    protected function _buildParams(Transaction $transaction)
    {
        $params = array(
            'type' => $this->_getNmiType($transaction),
            'username' => $this->getCredential('username'),
            'password' => $this->getCredential('password'),
        );
        
        if (in_array($transaction->getType()->getValue(), array(TransactionType::CardCapture, TransactionType::CardRefund, TransactionType::CardVoid))) {
            $params = array_merge($params, array(
                'transactionid' => $transaction->getParent()->getResult()->getExternalId(),
            ));
        }
        else {
            $account = $transaction->getAccount();            
            $params = array_merge($params, array(
                'ccnumber' => $account->getAccountNumber(),
                'ccexp' => $account->getExpMonth()->getLongMonth() . $account->getExpYear()->getShortYear(),
            ));
        }
        
        if ($transaction->getType()->getValue() != TransactionType::CardVoid) {
            $params['amount'] = $transaction->getAmount();
        }
                
        return $params;
    }
}