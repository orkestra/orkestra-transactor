<?php

namespace Orkestra\Transactor\Entity;

use Orkestra\Common\Type\Enum;

class TransactionType extends Enum
{
    /**
     * A Credit Card Sale
     */
    const CardSale = 'card.sale';
    
    /**
     * A Credit Card Authorization
     */
    const CardAuth = 'card.auth';
    
    /**
     * A Credit Card Capture
     */
    const CardCapture = 'card.capture';
    
    /**
     * A Credit Card Credit
     */
    const CardCredit = 'card.credit';
    
    /**
     * A Credit Card Refund
     */
    const CardRefund = 'card.refund';
    
    /**
     * A Credit Card Void
     */
    const CardVoid = 'card.void';
    
    /**
     * An ACH Request
     */    
    const AchRequest = 'ach.request';
    
    /**
     * An ACH Response
     */
    const AchResponse = 'ach.response';
    
    /**
     * An MFA Transfer
     */
    const MfaTransfer = 'mfa.transfer';
}