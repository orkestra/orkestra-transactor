<?php

namespace Orkestra\Transactor\DbalType;

use Orkestra\Common\DbalType\AbstractEnumType;

/**
 * Account Type EnumType
 *
 * Provides integration for the Account Type enumeration and Doctrine DBAL
 */
class AccountTypeEnumType extends AbstractEnumType
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $name = 'enum.orkestra.bank_account_type';

    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $class = 'Orkestra\Transactor\Entity\Account\BankAccount\AccountType';
}
