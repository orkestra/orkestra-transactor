<?php

namespace Orkestra\Transactor\Entity\Transaction;

use Orkestra\Common\Type\Enum;

/**
 * Network Type Enumeration
 *
 * Describes the different networks that a transaction may be processed on
 */
class NetworkType extends Enum
{
    /**
     * A credit card transaction
     */
    const CARD = 'Card';

    /**
     * An ACH transaction
     */
    const ACH = 'ACH';

    /**
     * A special type of ACH transaction for Master Funding Accounts
     */
    const MFA = 'MFA';

    /**
     * A cash transaction
     */
    const CASH = 'Cash';

    /**
     * A paper check transaction
     */
    const CHECK = 'Check';

    /**
     * A point transaction
     */
    const POINTS = 'Points';
}
