# Upgrade from 1.1 to 1.2

1.2 brought support for common payment processors and extended support for swiped card information decoding.

### BC breaks
* `TransactorInterface` gained an additional method `createCredentials` that all Transactors must implement.
    * This method is intended to create a default set of credentials for the given transactor.
* An additional account entity, `EncryptedSwipedCardAccount`, was introduced.
* A `SwipedCardAccount` no longer populates account information when track data is set.
    * Instead, use an appropriate Track Decoder implementation.


# Upgrade from 1.0 to 1.1

1.1 features Swiped credit card transactions and changes to make the API more consistent with other PSR projects.

### BC breaks
* NMI Card Transactor
    * `postUrl` option changed to `post_url`
    * All protected methods are no longer prefixed with an underscore
* PaymentsXpress ACH Transactor
    * `postUrl` option changed to `post_url`, `testMode` to `test_mode`
    * All protected methods are no longer prefixed with an underscore
* `AbstractTransactor` no longer disables the OptionsResolver by default
* `TransactorFactory::$_transactors` was renamed to `TransactorFactory::$transactors` and made private.
* `AbstractTransactor::$_supportedNetworks` and `$_supportedTypes` are now `$supportedNetworks` and `$supportedTypes`.
