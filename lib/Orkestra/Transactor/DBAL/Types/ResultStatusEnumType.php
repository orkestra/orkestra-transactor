<?php

namespace Orkestra\Transactor\DBAL\Types;

use Orkestra\Common\DBAL\Types\EnumTypeBase;

/**
 * Result Type EnumType
 *
 * Provides integration for the Result Type enumeration and Doctrine DBAL
 */
class ResultStatusEnumType extends EnumTypeBase
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $_name = 'enum.orkestra.result_status';

    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $_class = 'Orkestra\Transactor\Entity\Result\ResultStatus';
}
