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

/**
 * A points account
 */
interface PointsAccountInterface
{
    /**
     * Adds $adjustment to the balance
     *
     * @param int $adjustment
     */
    public function adjustBalance($adjustment);

    /**
     * Gets the account balance
     *
     * @return int
     */
    public function getBalance();

    /**
     * Sets the account balance
     *
     * @param int $balance
     */
    public function setBalance($balance);
}