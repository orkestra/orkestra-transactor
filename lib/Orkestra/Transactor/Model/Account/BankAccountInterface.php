<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Model\Account;

use Orkestra\Transactor\Model\Account\BankAccount\AccountType;

/**
 * A bank account
 */
interface BankAccountInterface
{
    /**
     * Gets the routing number
     *
     * @return string $accountNumber
     */
    public function getAccountNumber();

    /**
     * Sets the routing number
     *
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber);
    
    /**
     * Gets the routing number
     *
     * @return string $routingNumber
     */
    public function getRoutingNumber();

    /**
     * Sets the routing number
     *
     * @param string $routingNumber
     */
    public function setRoutingNumber($routingNumber);

    /**
     * Gets the account type
     *
     * @return AccountType $accountType
     */
    public function getAccountType();

    /**
     * Sets the account type
     *
     * @param AccountType $accountType
     */
    public function setAccountType(AccountType $accountType);
}