<?php

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
     * A Credit Card Sale
     */
    const CARD_SALE = 'Card Sale';

    /**
     * A Credit Card Authorization
     */
    const CARD_AUTH = 'Card Auth';

    /**
     * A Credit Card Capture
     */
    const CARD_CAPTURE = 'Card Capture';

    /**
     * A Credit Card Credit
     */
    const CARD_CREDIT = 'Card Credit';

    /**
     * A Credit Card Refund
     */
    const CARD_REFUND = 'Card Refund';

    /**
     * A Credit Card Void
     */
    const CARD_VOID = 'Card Void';

    /**
     * An ACH Request
     */
    const ACH_REQUEST = 'ACH Request';

    /**
     * An ACH Response
     */
    const ACH_RESPONSE = 'ACH Response';

    /**
     * An MFA Transfer
     */
    const MFA_TRANSFER = 'MFA Transfer';
}
