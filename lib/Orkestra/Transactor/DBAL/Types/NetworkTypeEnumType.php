<?php

namespace Orkestra\Transactor\DBAL\Types;

use Orkestra\Common\DBAL\Types\EnumTypeBase;

/**
 * Network Type EnumType
 *
 * Provides integration for the Network Type enumeration and Doctrine DBAL
 */
class NetworkTypeEnumType extends EnumTypeBase
{
    /**
     * @var string The unique name for this EnumType
     */
    protected $_name = 'enum.orkestra.network_type';

    /**
     * @var string The fully qualified class name of the Enum that this class wraps
     */
    protected $_class = 'Orkestra\Transactor\Entity\Transaction\NetworkType';
}
