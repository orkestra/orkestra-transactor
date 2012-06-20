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
 * ACH transactor for the PaymentsXpress payment processing gateway
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
        // Transaction\TransactionType::CREDIT,
        // Transaction\TransactionType::AUTH,
        // Transaction\TransactionType::CAPTURE,
        // Transaction\TransactionType::REFUND,
        // Transaction\TransactionType::VOID,
        Transaction\TransactionType::QUERY,
        // Transaction\TransactionType::UPDATE,
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

        $result = $transaction->getResult();

        $data = Transaction\TransactionType::QUERY !== $transaction->getType()->getValue()
            ? json_decode($response->getContent())
            : $this->_normalizeQueryResponse($response->getContent());

        if (null === $data) {
            $result->setType(new Result\ResultType(Result\ResultType::ERROR));
            $result->setMessage('An error occurred while contacting the PaymentsXpress system');
        } else {
            if ('Approved' !== $data->CommandStatus) {
                $result->setType(new Result\ResultType('Declined' !== $data->CommandStatus ? Result\ResultType::ERROR : Result\ResultType::DECLINED));
                $result->setMessage(!empty($data->ErrorInformation) ? $data->Description . ': ' . $data->ErrorInformation : $data->Description);

                if (!empty($data->TransAct_ReferenceID)) {
                    $result->setExternalId($data->TransAct_ReferenceID);
                }
            } else {
                if (Transaction\TransactionType::QUERY === $transaction->getType()->getValue()) {
                    $this->_handleQueryResponse($transaction, $data);
                } else {
                    $result->setType(new Result\ResultType(Result\ResultType::PENDING));
                }

                $result->setExternalId($data->TransAct_ReferenceID);
            }
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
     * @param array $options
     *
     * @throws \RuntimeException
     * @return array
     */
    protected function _buildParams(Transaction $transaction, $options)
    {
        $credentials = $transaction->getCredentials();
        $account = $transaction->getAccount();

        $params = $params = array(
            'ProviderID' => $credentials->getCredential('providerId'),
            'Provider_GateID' => $credentials->getCredential('providerGateId'),
            'Provider_GateKey' => $credentials->getCredential('providerGateKey'),
            'Command' => $this->_getCommand($transaction),
            'CommandVersion' => '1.0',
            'TestMode' => !empty($options['testMode']) ? 'On' : 'Off',
            'MerchantID' => $credentials->getCredential('merchantId'),
            'Merchant_GateID' => $credentials->getCredential('merchantGateId'),
            'Merchant_GateKey' => $credentials->getCredential('merchantGateKey'),
        );

        $transactionType = $transaction->getType()->getValue();

        if (Transaction\TransactionType::SALE === $transactionType) {
            $params = array_merge($params, array(
                'ResponseType' => 'JSON',
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
                'RoutingNumber' => $account->getRoutingNumber(),
                'AccountNumber' => $account->getAccountNumber(),
                'Billing_CustomerName' => $account->getName(),
                'Billing_Address1' => $account->getAddress(),
                'Billing_City' => $account->getCity(),
                'Billing_State' => $account->getRegion(),
                'Billing_Zip' => $account->getPostalCode(),
                'Billing_Phone' => $account->getPhoneNumber(),
                'SendEmailToCustomer' => 'No',
                'Run_ExpressVerify' => 'No',
                'SECCode' => 'WEB',
                'Customer_IPAddress' => $account->getIpAddress(),
            ));
        } elseif (Transaction\TransactionType::QUERY === $transactionType) {
            $params['TrackingDate'] = !empty($options['date']) ? $options['date'] : date('mdY');
        } else {
            throw new \RuntimeException(sprintf('The transaction type %s is not yet implemented', $transaction->getType()->getValue()));
        }

        return $params;
    }

    /**
     * @param Transaction $transaction
     *
     * @return string
     */
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
     * Handles a query response
     *
     * @param Transaction $transaction
     * @param object $data
     */
    protected function _handleQueryResponse(Transaction $transaction, $data)
    {
        $result = $transaction->getResult();
        $parentResult = $transaction->getParent()->getResult();
        $eventResult = null;

        foreach ($data->Results as $event) {
            if ($event->TransAct_ReferenceID === $parentResult->getExternalId()) {
                $eventResult = $event;

                // We don't break in case the transaction has multiple events in the same query-- we want the latest
            }
        }

        $resultType = $parentResult->getType()->getValue();

        if (null !== $eventResult) {
            switch ($eventResult->ResultingStatus) {
                case 'Scheduled':
                    $resultType = Result\ResultType::PENDING;
                    break;

                case 'Cancelled':
                    $resultType = Result\ResultType::CANCELLED;
                    break;

                case 'In-Process':
                    $resultType = Result\ResultType::PROCESSED;
                    break;

                case 'Cleared':
                    $resultType = Result\ResultType::APPROVED;
                    break;

                case 'Failed Verification':
                case 'Returned-NSF':
                case 'Returned-Other':
                    $resultType = Result\ResultType::DECLINED;
                    break;

                case 'Charged Back':
                    $resultType = Result\ResultType::CHARGED_BACK;
                    break;

                case 'Merchant Hold':
                case 'Processor Hold':
                    $resultType = Result\ResultType::HOLD;
                    break;
            }
        }

        $result->setType(new Result\ResultType($resultType));
    }

    /**
     * Normalizes a Query response, converting it from CSV to an object
     *
     * @param $content
     *
     * @return null|object
     */
    protected function _normalizeQueryResponse($content)
    {
        $result = new \stdClass();
        $result->Results = array();

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $parts = str_getcsv($line);

            if ($parts[0] === 'Command Response') {
                $result->CommandStatus = $parts[1];
                $result->ResponseCode = $parts[2];
                $result->Description = $parts[3];
                $result->ErrorInformation = $parts[4];
                $result->TransAct_ReferenceID = $parts[5];

                continue;
            }

            $status = new \stdClass();
            $status->TransAct_ReferenceID = $parts[0];
            $status->Provider_TransactionID = $parts[1];
            $status->MerchantID = $parts[2];
            $status->EventName = $parts[3];
            $status->EventDate = $parts[4];
            $status->ResultingStatus = $parts[5];
            $status->ReturnCode = $parts[6];
            $status->VerificationStatus = $parts[7];
            $status->VerificationCode = $parts[8];
            $status->VerificationText = $parts[9];

            $result->Results[] = $status;
        }

        if (!$result->CommandStatus) {
            return null;
        }

        return $result;
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
