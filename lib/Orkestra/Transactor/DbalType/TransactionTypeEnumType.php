<?php

namespace Orkestra\Transactor\DbalType;

use Orkestra\Common\DbalType\AbstractEnumType;

/**
 * Transaction Type EnumType
 *
 * Provides integration for the Transaction Type enumeration and Doctrine DBAL
 */
class TransactionTypeEnumType extends AbstractEnumType
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $name = 'enum.orkestra.transaction_type';

    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $class = 'Orkestra\Transactor\Entity\Transaction\TransactionType';
}
