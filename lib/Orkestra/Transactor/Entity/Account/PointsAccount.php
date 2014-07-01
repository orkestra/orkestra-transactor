<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Model\Account\PointsAccountInterface;

/**
 * Represents a points account, to be used with the Points network
 *
 * @ORM\Entity
 */
class PointsAccount extends AbstractAccount implements PointsAccountInterface
{
    protected $accountNumber = '';

    /**
     * @var int
     *
     * @ORM\Column(name="balance", type="integer")
     */
    protected $balance = 0;

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = (string) $accountNumber;
    }

    /**
     * Adds $adjustment to the balance
     *
     * @param int $adjustment
     */
    public function adjustBalance($adjustment)
    {
        $this->balance += (int) $adjustment;
    }

    /**
     * Gets the account balance
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Sets the account balance
     *
     * @param int $balance
     */
    public function setBalance($balance)
    {
        $this->balance = (int) $balance;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Points';
    }
}
