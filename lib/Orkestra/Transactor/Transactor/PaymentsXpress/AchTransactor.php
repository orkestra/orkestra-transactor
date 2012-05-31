<?php

namespace Orkestra\Transactor\Transactor\PaymentsXpress;

use Symfony\Component\HttpFoundation\Request;

use Orkestra\Transactor\AbstractTransactor;
use Orkestra\Common\Kernel\HttpKernel;
use Orkestra\Transactor\Entity\Account\BankAccount\AccountType;
use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Exception\ValidationException;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Entity\Result;

/**
 * ACH transactor for the Payments Xpress payment processing gateway
 */
class AchTransactor extends AbstractTransactor
{
    /**
     * @var array
     */
    protected static $_supportedNetworks = array(
        Transaction\NetworkType::ACH,
    );

    /**
     * @var array
     */
    protected static $_supportedTypes = array(
        Transaction\TransactionType::SALE,
        Transaction\TransactionType::CREDIT,
        Transaction\TransactionType::AUTH,
        Transaction\TransactionType::CAPTURE,
        Transaction\TransactionType::REFUND,
        Transaction\TransactionType::VOID,
        Transaction\TransactionType::QUERY,
        Transaction\TransactionType::UPDATE,
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
    protected function _doTransact(Transaction $transaction, $options = array())
    {
        $this->_validateTransaction($transaction);
        $params = $this->_buildParams($transaction, $options);

        $postUrl = !empty($options['postUrl']) ? $options['postUrl'] : 'https://www.paymentsxpress.com/pxgateway/datalinks/transact.aspx';
        $request = Request::create($postUrl, 'POST', $params);
        $response = $this->_kernel->handle($request);

        $data = json_decode($response->getContent());
        $result = $transaction->getResult();
        if ('Approved' !== $data->CommandStatus) {
            $result->setType(new Result\ResultType('Declined' !== $data->CommandStatus ? Result\ResultType::ERROR : Result\ResultType::DECLINED));
            $result->setMessage($data->Description . ': ' . $data->ErrorInformation);

            if (!empty($data->TransAct_ReferenceID)) {
                $result->setExternalId($data->TransAct_ReferenceID);
            }
        } else {
            $result->setType(new Result\ResultType(Result\ResultType::APPROVED));
            $result->setExternalId($data->TransAct_ReferenceID);
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
        if (!$transaction->getParent() && in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::CAPTURE,
            Transaction\TransactionType::REFUND,
            Transaction\TransactionType::VOID,
            Transaction\TransactionType::QUERY,
            Transaction\TransactionType::UPDATE))
        ) {
            throw ValidationException::parentTransactionRequired();
        }

        $credentials = $transaction->getCredentials();

        if (!$credentials) {
            throw ValidationException::missingCredentials();
        } elseif (null === $credentials->getCredential('providerId')) {
            throw ValidationException::missingRequiredParameter('Provider ID');
        } elseif (null === $credentials->getCredential('providerGateId')) {
            throw ValidationException::missingRequiredParameter('Provider Gate ID');
        } elseif (null === $credentials->getCredential('providerGateKey')) {
            throw ValidationException::missingRequiredParameter('Provider Gate Key');
        } elseif (null === $credentials->getCredential('merchantId')) {
            throw ValidationException::missingRequiredParameter('Merchant ID');
        } elseif (null === $credentials->getCredential('merchantGateId')) {
            throw ValidationException::missingRequiredParameter('Merchant Gate ID');
        } elseif (null === $credentials->getCredential('merchantGateKey')) {
            throw ValidationException::missingRequiredParameter('Merchant Gate Key');
        }

        $account = $transaction->getAccount();

        if (!$account || !$account instanceof BankAccount) {
            throw ValidationException::missingAccountInformation();
        }

        if (null === $account->getAccountNumber()) {
            throw ValidationException::missingRequiredParameter('account number');
        } elseif (null === $account->getRoutingNumber()) {
            throw ValidationException::missingRequiredParameter('routing number');
        } elseif (null === $account->getIpAddress()) {
            throw ValidationException::missingRequiredParameter('IP address');
        } elseif (null === $account->getAccountType()) {
            throw ValidationException::missingRequiredParameter('account type');
        }
    }

    /**
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @return array
     */
    protected function _buildParams(Transaction $transaction, $options)
    {
        $credentials = $transaction->getCredentials();
        $account = $transaction->getAccount();

        $params = array(
            'ProviderID' => $credentials->getCredential('providerId'),
            'Provider_GateID' => $credentials->getCredential('gateId'),
            'Provider_GateKey' => $credentials->getCredential('gateKey'),
            'Command' => $this->_getCommand($transaction),
            'CommandVersion' => '1.0',
            'TestMode' => !empty($options['testMode']) ? true : false,
            'ResponseType' => 'JSON'
        );

        if (Transaction\TransactionType::SALE === $transaction->getType()->getValue()) {
            $params = array_merge($params, array(
                'PaymentDirection' => 'FromCustomer',
                'Amount' => $transaction->getAmount(),
                'CheckType' => in_array($account->getAccountType()->getValue(), array(
                    AccountType::PERSONAL_SAVINGS,
                    AccountType::PERSONAL_CHECKING
                )) ? 'Personal' : 'Business',
                'AccountType' => in_array($account->getAccountType()->getValue(), array(
                    AccountType::PERSONAL_SAVINGS,
                    AccountType::BUSINESS_SAVINGS
                )) ? 'Savings' : 'Checking',
                'Billing_CustomerName' => $account->getName(),
                'Billing_Address1' => $account->getAddress(),
                'Billing_City' => $account->getCity(),
                'Billing_State' => $account->getRegion(),
                'Billing_Zip' => $account->getPostalCode(),
                'Billing_Phone' => $account->getPhoneNumber(),
                'SendEmailToCustomer' => 'No',
                'Run_ExpressVerify' => 'No',
                'SECCode' => 'WEB',
            ));
        } else {
            throw new \RuntimeException(sprintf('The transaction type %s is not yet implemented', $transaction->getType()->getValue()));
        }

        return $params;
    }

    protected function _getCommand(Transaction $transaction)
    {
        switch ($transaction->getType()->getValue()) {
            case Transaction\TransactionType::SALE:
                return 'ECheck.ProcessPayment';

            case Transaction\TransactionType::AUTH:
                return 'ECheck.Authorize';

            case Transaction\TransactionType::CAPTURE:
                return 'ECheck.Capture';

            case Transaction\TransactionType::REFUND:
                return 'ECheck.Refund';

            case Transaction\TransactionType::VOID:
                return 'ECheck.Void';

            case Transaction\TransactionType::QUERY:
                return 'ECheckReports.StatusTrackingQuery';

            case Transaction\TransactionType::UPDATE:
                return 'ECheck.Update';
        }
    }

    /**
     * Returns the internally used type of this Transactor
     *
     * @return string
     */
    function getType()
    {
        return 'orkestra.payments_xpress.ach';
    }

    /**
     * Returns the name of this Transactor
     *
     * @return string
     */
    function getName()
    {
        return 'Payments Xpress ACH Gateway';
    }

}
