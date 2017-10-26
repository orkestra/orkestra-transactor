Orkestra Transactor
===================

[![Build Status](https://travis-ci.org/orkestra/orkestra-transactor.png?branch=master)](https://travis-ci.org/orkestra/orkestra-transactor)

The Transactor provides payment processing functionality to any PHP 5.4+, 7.0+ project. This library contains dependencies on Symfony and supports both Symfony 2.3+ and 3.0+.

> HHVM is loosely supported, but it has not been extensively tested. Please [Report an Issue](https://github.com/orkestra/orkestra-transactor/issues/new) if you have any problems.

### Supported payment processors:

* [Network Merchants](https://nmi.com) credit card, ACH, and swiped transactions.
* [Authorize.Net](https://www.authorize.net/) credit card and ACH support.
* Cash and check transactions for brick and mortar stores.
* Points, useful as a means to keep track of account credit.

## Installation

Install this project using [Composer](https://getcomposer.org).

Add orkestra-transactor to your project by running `composer require orkestra/transactor:~1.2`, or by adding it to your `composer.json` file:

``` json
{
    "require": {
        "orkestra/transactor": "~1.2"
    }
}
```

Then run `composer install` or `composer update`.


## Usage

### Overview

1. Create Credentials
2. Create Account
3. Create Transaction
4. Process Transaction

#### 1. Create Credentials

Credentials are used by a Transactor to authenticate with the remote system. Each transactor has specific fields that are necessary to allow processing of transactions.

You can use a given Transactor to create a default set of Credentials by calling `$transactor->createCredentials()`.

``` php
<?php

use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Transactor\Generic\GenericTransactor;

$transactor = new GenericTransactor();

$creds = $transactor->createCredentials();
foreach ($creds->getCredentials() as $k => $v) {
    // Enumerate the default fields required by the transactor.
}

// Update credentials as necessary
$creds->setCredential('username', 'myuser');
$creds->setCredential('password', 'mypass');
```

*Tip:* You can inspect a Credentials entity and get its Transactor type, then pass that to the TransactorFactory to dynamically load the appropriate Transactor for a given Transaction.


#### 2. Create Account

An Account is essentially a container of customer information. There are multiple types of Account entities, basically one type per Network type. Different Transactors support different networks. A Network is the method by which to process a transaction, such as Credt Card, ACH, or Cash.

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
$transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::CASH));
$transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
```


#### 4. Transact

Use the Transactor to actually process the Transaction.

``` php
<?php

$result = $transactor->transact($transaction);
```
