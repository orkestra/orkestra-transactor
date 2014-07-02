<?php

namespace Orkestra\Transactor\Serializer\AuthorizeNet\Card;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Orkestra\Transactor\Entity\Account\BankAccount;
use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TransactionNormalizer implements NormalizerInterface
{
    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $transaction   event to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array
     */
    public function normalize($transaction, $format = null, array $context = array())
    {
        /** @var  $transaction Transaction */
        $credentials = $transaction->getCredentials();

        $createTransactionRequest = array();

        if ($format == 'xml') {
            $createTransactionRequest['@xmlns'] = 'AnetApi/xml/v1/schema/AnetApiSchema.xsd';
        }

        $merchantAuthentication = array(
            'name' => $credentials->getCredential('username'),
            'transactionKey' => $credentials->getCredential('password'),
        );

        $transactionRequest = array(
            "transactionType" => $this->getTransactionType($transaction)
        );

        if ($transaction->getType()->getValue() != TransactionType::VOID) {
            $transactionRequest['amount'] = $transaction->getAmount();
        }

        $account = $transaction->getAccount();
        if (in_array($transaction->getType()->getValue(), array(
            TransactionType::SALE,
            TransactionType::AUTH,
            TransactionType::REFUND))
        ){
            $payment = array();
            if ($account instanceof CardAccount) {
                if ($account instanceof SwipedCardAccount
                    && $transaction->getType()->getValue() != TransactionType::REFUND
                ){
                    $payment['trackData'] = array();
                    if ($account->getTrackOne()) {
                        $payment['trackData']['track1'] = $account->getTrackOne();
                    } elseif ($account->getTrackTwo()) {
                        $payment['trackData']['track2'] = $account->getTrackTwo();
                    }
                } else {
                    $payment = array(
                        'creditCard' => array(
                            'cardNumber' => $account->getAccountNumber(),
                            'expirationDate' => $account->getExpMonth()->getLongMonth() . $account->getExpYear()->getShortYear(),
                        )
                    );

                    if (isset($context['enable_cvv'])
                        && true === $context['enable_cvv']
                        && $transaction->getType()->getValue() != TransactionType::REFUND) {
                        $payment['creditCard']['cardCode'] = $account->getCvv();
                    }
                }
            } elseif ($account instanceof BankAccount) {
                $payment = array(
                    'bankAccount' => array(
                        'accountType' => 'checking',
                        'routingNumber' => $account->getRoutingNumber(),
                        'accountNumber' => $account->getAccountNumber(),
                        'nameOnAccount' => $account->getName()
                    )
                );
            } else {
                throw new InvalidArgumentException(sprintf("Unsupported Account Type %s", get_class($account)));
            }

            $transactionRequest['payment'] = $payment;
        }

        if ($context['event_id']) {
            $transactionRequest['order'] = array();
            $transactionRequest['order']['invoiceNumber'] = $context['event_id'];
        }


        if (in_array($transaction->getType()->getValue(), array(
            TransactionType::CAPTURE,
            TransactionType::REFUND,
            TransactionType::VOID))
        ) {
            $transactionRequest['refTransId'] = $transaction->getParent()->getResult()->getExternalId();
        }


        if (in_array($transaction->getType()->getValue(), array(
            TransactionType::SALE,
            TransactionType::AUTH))
        ) {
                $names = explode(' ', $account->getName(), 2);
                $firstName = isset($names[0]) ? $names[0] : '';
                $lastName = isset($names[1]) ? $names[1] : '';

                if (isset($context['email'])) {
                    $transactionRequest['customer'] = array(
                        'email' => $context['email']
                    );
                }

                $transactionRequest['billTo'] = array(
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'address' => $account->getAddress(),
                    'city' => $account->getCity(),
                    'state' => $account->getRegion(),
                    'zip' => $account->getPostalCode(),
                    'country' => $account->getCountry(),
                );
        }

        if ($account instanceof SwipedCardAccount
            && !in_array($transaction->getType()->getValue(), array(TransactionType::REFUND, TransactionType::VOID))
        ){
            $transactionRequest['retail'] = array(
                'marketType' => 2,
                'deviceType' => 7
            );
        }

        if (isset($context['test']) && $context['test'])  {
            $transactionRequest['transactionSettings']['setting'][] = array(
                'settingName' => 'testRequest',
                'settingValue' => 'true'
            );
        }

        if (isset($context['userFields'])) {
            $transactionRequest['userFields'] = array();
            $transactionRequest['userFields']['userField'] = array();
            foreach ($context['userFields'] as $name => $value) {
                $transactionRequest['userFields']['userField'][] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        $createTransactionRequest['merchantAuthentication'] = $merchantAuthentication;
        $createTransactionRequest['transactionRequest'] = $transactionRequest;

        return $createTransactionRequest;
    }

    /**
     * @param  \Orkestra\Transactor\Entity\Transaction $transaction
     * @return string
     */
    private function getTransactionType(Transaction $transaction)
    {
        switch ($transaction->getType()->getValue()) {
            case TransactionType::SALE:
                return 'authCaptureTransaction';
            case TransactionType::AUTH:
                return 'authOnlyTransaction';
            case TransactionType::CAPTURE:
                return 'priorAuthCaptureTransaction';
            case TransactionType::CREDIT:
                return 'creditTransaction';
            case TransactionType::REFUND:
                return 'refundTransaction';
            case TransactionType::VOID:
                return 'voidTransaction';
        }
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed  $data   Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return Boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Transaction;
    }
}
