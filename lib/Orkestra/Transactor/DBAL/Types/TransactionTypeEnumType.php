<?php

namespace Orkestra\Transactor\DBAL\Types;

use Orkestra\Common\DBAL\Types\EnumTypeBase;

/**
 * Transaction Type EnumType
 *
 * Provides integration for the Transaction Type enumeration and Doctrine DBAL
 */
class TransactionTypeEnumType extends EnumTypeBase
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $_name = 'enum.orkestra.transaction_type';
    
    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $_class = 'Orkestra\Transactor\Entity\TransactionType';
}