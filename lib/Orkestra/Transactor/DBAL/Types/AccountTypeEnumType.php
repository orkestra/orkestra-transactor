<?php

namespace Orkestra\Transactor\DBAL\Types;

use Orkestra\Common\DBAL\Types\EnumTypeBase;

/**
 * Account Type EnumType
 *
 * Provides integration for the Account Type enumeration and Doctrine DBAL
 */
class AccountTypeEnumType extends EnumTypeBase
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $_name = 'enum.orkestra.bank_account_type';
    
    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $_class = 'Orkestra\Transactor\Entity\TransactionType';
}