Orkestra Transactor
===================

[![Build Status](https://travis-ci.org/orkestra/orkestra-transactor.png?branch=master)](https://travis-ci.org/orkestra/orkestra-transactor)

The Transactor provides payment processing functionality to any PHP project.

### Supported payment processors:

* Network Merchants credit card processing API supporting swiped and keyed transactions
* Payments Xpress ACH processing API
* Cash and check transactions for brick and mortar stores
* Points, or account credit

## Installation

The easiest way to add orkestra-transactor to your project is using composer.

Add orkestra-transactor to your `composer.json` file:

``` json
{
    "require": {
        "orkestra/transactor": "dev-master"
    }
}
```

Then run `composer install` or `composer update`.


## Usage

### Overview

1. Create Credentials
2. Create Account
3. Create Transaction
4. Transact

#### 1. Create Credentials

Credentials are used by a Transactor to authenticate with the remote system. Each transactor has specific fields that
are necessary to allow processing of transactions.

**TODO:** There is currently no way to programmatically enumerate what fields are necessary.

``` php
<?php

use Orkestra\Transactor\Entity\Credentials;

$creds = new Credentials();
$creds->setTransactor($someTransactor);
$creds->setCredential('username', 'myuser');
$creds->setCredential('password', 'mypass');
```


#### 2. Create Account

An Account is essentially a container of customer information. There are multiple types of Account entities, basically
one type per Network type. Different Transactors support different networks. A Network is the method by which to process
a transaction, such as Credt Card, ACH, or Cash.

``` php
<?php

use Orkestra\Transactor\Entity\Account;

// A credit card
$card = new Account\CardAccount();
$card->setAccountNumber('4111111111111111');

// A bank account (used for ACH processing)
$account = new Account\BankAccount();
$account->setAccountNumber('12345777');
$account->setRoutingNumber('5556713355');
```


#### 3. Create Transaction

A Transaction must provide the Transactor a proper Account and Credentials to allow the Transactor to process.

``` php
<?php

use Orkestra\Transactor\Entity\Transaction;

$transaction = new Transaction();
$transaction->setAccount($account);
$transaction->setCredentials($creds);
$transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH));
$transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
```


#### 4. Transact

Load the appropriate transactor.

*Tip:* You can inspect a Credentials entity and get its Transactor type, then pass that to the TransactorFactory to
dynamically load the appropriate Transactor for a given Transaction.

``` php
<?php

use Orkestra\Transactor\Transactor;

$transactor = new PaymentsXpress\AchTransactor();

$result = $transactor->transact($transaction);
```
