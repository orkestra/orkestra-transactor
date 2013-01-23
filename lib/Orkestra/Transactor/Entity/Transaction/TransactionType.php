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
 * Transaction Type Enumeration
 *
 * Describes the different types of transactions
 */
class TransactionType extends Enum
{
    /**
     * A sale transaction
     */
    const SALE = 'Sale';

    /**
     * An authorization transaction
     */
    const AUTH = 'Auth';

    /**
     * A capture transaction
     */
    const CAPTURE = 'Capture';

    /**
     * A credit transaction
     */
    const CREDIT = 'Credit';

    /**
     * A refund transaction
     */
    const REFUND = 'Refund';

    /**
     * A void transaction
     */
    const VOID = 'Void';

    /**
     * A transaction that queries for the current status of the parent transaction
     */
    const QUERY = 'Query';

    /**
     * An transaction that updates the parent transaction
     */
    const UPDATE = 'Update';
}
