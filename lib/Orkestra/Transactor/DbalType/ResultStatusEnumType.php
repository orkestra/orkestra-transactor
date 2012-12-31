<?php

namespace Orkestra\Transactor\DbalType;

use Orkestra\Common\DbalType\AbstractEnumType;

/**
 * Result Type EnumType
 *
 * Provides integration for the Result Type enumeration and Doctrine DBAL
 */
class ResultStatusEnumType extends AbstractEnumType
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $name = 'enum.orkestra.result_status';

    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $class = 'Orkestra\Transactor\Entity\Result\ResultStatus';
}
