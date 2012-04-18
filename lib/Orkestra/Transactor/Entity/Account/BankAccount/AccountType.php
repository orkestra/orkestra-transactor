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
    const PersonalSavings = 'Personal Savings';
    
    /**
     * A personal checking account
     */
    const PersonalChecking = 'Personal Checking';
    
    /**
     * A business savings account
     */
    const BusinessSavings = 'Business Savings';
    
    /**
     * A business checking account
     */
    const BusinessChecking = 'Business Checking';
}