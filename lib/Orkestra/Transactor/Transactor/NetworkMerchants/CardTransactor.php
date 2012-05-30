<?php

namespace Orkestra\Transactor\Transactor\NetworkMerchants;

use Symfony\Component\HttpFoundation\Request;
use Orkestra\Common\Kernel\HttpKernel;

use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Exception\ValidationException;

/**
 * Credit card transactor for the Network Merchants payment processing gateway
 */
class CardTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $_supportedNetworks = array(
        Transaction\NetworkType::CARD,
    );

    /**
     * @var array
     */
    protected static $_supportedTypes = array(
        Transaction\TransactionType::SALE,
        Transaction\TransactionType::AUTH,
        Transaction\TransactionType::CAPTURE,
        Transaction\TransactionType::CREDIT,
        Transaction\TransactionType::REFUND,
        Transaction\TransactionType::VOID,
    );

    /**
     * @var \Orkestra\Common\Kernel\HttpKernel
     */
    protected $_kernel;

    /**
     * Constructor
     *
     * @param \Orkestra\Common\Kernel\HttpKernel $kernel
     */
    public function __construct(HttpKernel $kernel)
    {
        $this->_kernel = $kernel;
    }

    /**
     * Transacts the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array $options
     *
     * @return \Orkestra\Transactor\Entity\Result
     */
    public function _doTransact(Transaction $transaction, $options = array())
    {
        $this->_validateTransaction($transaction);
        $params = $this->_buildParams($transaction);

        $postUrl = !empty($options['postUrl']) ? $options['postUrl'] : 'https://secure.networkmerchants.com/api/transact.php';
        $request = Request::create($postUrl, 'POST', $params);
        $response = $this->_kernel->handle($request);

        $result = $transaction->getResult();
        $data = array();
        parse_str($response->getContent(), $data);

        if (empty($data['response']) || '1' != $data['response']) {
            $result->setType(new Result\ResultType('2' == $data['response'] ? Result\ResultType::DECLINED : Result\ResultType::ERROR));
            $result->setMessage(empty($data['responsetext']) ? 'An unknown error occurred.' : $data['responsetext']);

            if (!empty($data['transactionid'])) {
                $result->setExternalId($data['transactionid']);
            }
        } else {
            $result->setType(new Result\ResultType(Result\ResultType::APPROVED));
            $result->setExternalId($data['transactionid']);
        }

        $result->setData('request', $params);
        $result->setData('response', $data);

        return $result;
    }

    /**
     * Validates the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     *
     * @throws \Orkestra\Transactor\Exception\ValidationException
     */
    protected function _validateTransaction(Transaction $transaction)
    {
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(Transaction\TransactionType::CAPTURE, Transaction\TransactionType::REFUND, Transaction\TransactionType::VOID))) {
            throw ValidationException::parentTransactionRequired();
        }

        $credentials = $transaction->getCredentials();

        if (!$credentials) {
            throw ValidationException::missingCredentials();
        } elseif (null === $credentials->getCredential('username') || null === $credentials->getCredential('password')) {
            throw ValidationException::missingRequiredParameter('username or password');
        }

        $account = $transaction->getAccount();

        if (!$account || !$account instanceof CardAccount) {
            throw ValidationException::missingAccountInformation();
        }

        if (null === $account->getAccountNumber()) {
            throw ValidationException::missingRequiredParameter('account number');
        } elseif (null === $account->getExpMonth() || null === $account->getExpYear()) {
            throw ValidationException::missingRequiredParameter('card expiration');
        }
    }

    /**
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @return string
     */
    protected function _getNmiType(Transaction $transaction)
    {
        switch ($transaction->getType()->getValue()) {
            case Transaction\TransactionType::SALE:
                return 'sale';
            case Transaction\TransactionType::AUTH:
                return 'auth';
            case Transaction\TransactionType::CAPTURE:
                return 'capture';
            case Transaction\TransactionType::CREDIT:
                return 'credit';
            case Transaction\TransactionType::REFUND:
                return 'refund';
            case Transaction\TransactionType::VOID:
                return 'void';
        }
    }

    /**
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @return array
     */
    protected function _buildParams(Transaction $transaction)
    {
        $credentials = $transaction->getCredentials();

        $params = array(
            'type' => $this->_getNmiType($transaction),
            'username' => $credentials->getCredential('username'),
            'password' => $credentials->getCredential('password'),
        );

        if (in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::CAPTURE,
            Transaction\TransactionType::REFUND,
            Transaction\TransactionType::VOID))
        ) {
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

        if ($transaction->getType()->getValue() != Transaction\TransactionType::VOID) {
            $params['amount'] = $transaction->getAmount();
        }

        return $params;
    }

    /**
     * Returns the internally used type of this Transactor
     *
     * @return string
     */
    public function getType()
    {
        return 'orkestra.network_merchants.card';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    public function getName()
    {
        return 'Network Merchants Credit Card Gateway';
    }
}
