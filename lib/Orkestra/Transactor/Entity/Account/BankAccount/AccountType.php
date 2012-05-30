<?php

namespace Orkestra\Transactor\Entity\Account\BankAccount;

use Orkestra\Common\Type\Enum;

/**
 * Account Type Enumeration
 *
 * Describes the different types of bank accounts
 */
class AccountType extends Enum
{
    /**
     * A personal savings account
     */
    const PERSONAL_SAVINGS = 'Personal Savings';

    /**
     * A personal checking account
     */
    const PERSONAL_CHECKING = 'Personal Checking';

    /**
     * A business savings account
     */
    const BUSINESS_SAVINGS = 'Business Savings';

    /**
     * A business checking account
     */
    const BUSINESS_CHECKING = 'Business Checking';
}
