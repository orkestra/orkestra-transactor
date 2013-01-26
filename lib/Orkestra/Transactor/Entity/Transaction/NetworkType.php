<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

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
     * A swiped credit card transaction
     */
    const SWIPED = 'Swiped Card';

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
