<?php

namespace Orkestra\Transactor\DbalType;

use Orkestra\Common\DbalType\AbstractEnumType;

/**
 * Network Type EnumType
 *
 * Provides integration for the Network Type enumeration and Doctrine DBAL
 */
class NetworkTypeEnumType extends AbstractEnumType
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $name = 'enum.orkestra.network_type';

    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $class = 'Orkestra\Transactor\Entity\Transaction\NetworkType';
}
