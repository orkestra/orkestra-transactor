<?php

namespace Orkestra\Transactor\Entity\Result;

use Orkestra\Common\Type\Enum;

/**
 * Result Type Enumeration
 *
 * Describes the different types of results
 */
class ResultType extends Enum
{
    /**
     * The transaction was approved
     */
    const APPROVED = 'Approved';

    /**
     * The transaction was declined
     */
    const DECLINED = 'Declined';

    /**
     * An error occurred
     */
    const ERROR = 'Error';
}
