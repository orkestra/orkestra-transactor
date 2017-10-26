<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Orkestra\Transactor\Entity\Account\CardAccount;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Transactor\NetworkMerchants;
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

$transactor = new NetworkMerchants\CardTransactor();

$credentials = $transactor->createCredentials();
/*
 * Credentials created through a transactor come initialized with default
 * values specific to that transactor. You can enumerate these default values:
 *
 * print_r($credentials->getCredentials());
 */

// Credentials support magic get/set
$credentials->username = 'demo';
$credentials->password = 'password';

$account = new CardAccount();
$account->setAccountNumber('4111111111111111');
$account->setExpMonth(new Month(10));
$account->setExpYear(new Year(2015));

// Create a Credit card Sale transaction
$transaction = new Transaction();
$transaction->setCredentials($credentials);
$transaction->setAccount($account);
$transaction->setAmount(2.50);
$transaction->setNetwork(new NetworkType(NetworkType::CARD));
$transaction->setType(new TransactionType(TransactionType::SALE));

$result = $transactor->transact($transaction);

if (!assert(ResultStatus::APPROVED == $result->getStatus())) {
    echo "Sales was " . $result->getStatus() . PHP_EOL;
    echo "Reason: " . $result->getMessage() . PHP_EOL;

    exit(1);
}

echo "Sale Approved, Transaction ID: " . $result->getExternalId() . PHP_EOL;


// Refund the sale
$refund = $transaction->createChild(new TransactionType(TransactionType::REFUND));

$result = $transactor->transact($refund);

if (!assert(ResultStatus::APPROVED == $result->getStatus())) {
    echo "Refund Declined. Reason: " . $result->getMessage() . PHP_EOL;

    exit(1);
}

echo "Refund Approved." . PHP_EOL;
