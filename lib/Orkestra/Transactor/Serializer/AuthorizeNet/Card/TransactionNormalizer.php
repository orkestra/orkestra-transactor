<?php

namespace Orkestra\Transactor\Serializer\AuthorizeNet\Card;

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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

        $createTransactionRequest['@xmlns'] = 'AnetApi/xml/v1/schema/AnetApiSchema.xsd';

        $merchantAuthentication = array(
            'name' => $credentials->getCredential('username'),
            'transactionKey' => $credentials->getCredential('password'),
        );

        $transactionRequest = array(
            "transactionType" => $this->getTransactionType($transaction)
        );

        if ($transaction->getType()->getValue() != Transaction\TransactionType::VOID) {
            $transactionRequest['amount'] = $transaction->getAmount();
        }

        if (in_array($transaction->getType()->getValue(), array(
            Transaction\TransactionType::CAPTURE,
            Transaction\TransactionType::REFUND,
            Transaction\TransactionType::VOID))
        ) {
            $transactionRequest = array_merge($transactionRequest, array(
                'refTransId' => $transaction->getParent()->getResult()->getExternalId(),
            ));
        } else {
            $account = $transaction->getAccount();

            if ($account instanceof SwipedCardAccount) {
                $transactionRequest = array_merge($transactionRequest, array(
                    'track1' => $account->getTrackOne(),
                    'track2' => $account->getTrackTwo(),
                ));
            } else {
                $payment = array(
                    'creditCard' => array(
                        'cardNumber' => $account->getAccountNumber(),
                        'expirationDate' => $account->getExpMonth()->getLongMonth() . $account->getExpYear()->getShortYear(),
                    )
                );

                if (isset($options['enable_cvv']) && true === $options['enable_cvv']) {
                    $payment['creditCard']['cardCode'] = $account->getCvv();
                }

                $transactionRequest['payment'] = $payment;

                if (isset($options['enable_avs']) && true === $options['enable_avs']) {
                    $names = explode(' ', $account->getName(), 2);
                    $firstName = isset($names[0]) ? $names[0] : '';
                    $lastName = isset($names[1]) ? $names[1] : '';

                    $transactionRequest['billTo'] = array(
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'address' => $account->getAddress(),
                        'city' => $account->getCity(),
                        'state' => $account->getRegion(),
                        'zip' => $account->getPostalCode(),
                        'country' => $account->getCountry(),
                    );
                    $transactionRequest['customerIP'] = $account->getIpAddress();
                }
            }
        }

        if ($context['test']) {
            $transactionRequest['transactionSettings']['setting'][] = array(
                'settingName' => 'testRequest',
                'settingValue' => 'true'
            );
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
            case Transaction\TransactionType::SALE:
                return 'authCaptureTransaction';
            case Transaction\TransactionType::AUTH:
                return 'authOnlyTransaction';
            case Transaction\TransactionType::CAPTURE:
                return 'priorAuthCaptureTransaction';
            case Transaction\TransactionType::CREDIT:
                return 'creditTransaction';
            case Transaction\TransactionType::REFUND:
                return 'refundTransaction';
            case Transaction\TransactionType::VOID:
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
