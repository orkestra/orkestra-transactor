# Upgrade from 1.0 to 1.1

### BC breaks
* NMI Card Transactor
    * `postUrl` option changed to `post_url`
* PaymentsXpress ACH Transactor
    * `postUrl` option changed to `post_url`, `testMode` to `test_mode`
* `AbstractTransactor` no longer disables the OptionsResolver by default
* `TransactorFactory::$_transactors` was renamed to `TransactorFactory::$transactors` and made private.
* `AbstractTransactor::$_supportedNetworks` and `$_supportedTypes` are now `$supportedNetworks` and `$supportedTypes`.
