Orkestra Transactor
===================

The Transactor provides payment processing functionality to any PHP project.

### Supported payment processors:

* Network Merchants credit card processing API
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

1. Create Credentials
2. Create Account
3. Create Transaction
4. Transact
