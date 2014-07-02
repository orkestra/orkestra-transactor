<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Model\Account\BankAccount;

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
