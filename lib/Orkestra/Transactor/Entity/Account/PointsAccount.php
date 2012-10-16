<?php

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Entity\Account\BankAccount\AccountType;

/**
 * Represents a points account, to be used with the Points network
 *
 * @ORM\Entity
 */
class PointsAccount extends AbstractAccount
{
    /**
     * @var int
     *
     * @ORM\Column(name="balance", type="integer")
     */
    protected $balance = 0;

    /**
     * Adds $adjustment to the balance
     *
     * @param int $adjustment
     */
    public function adjustBalance($adjustment)
    {
        $this->balance += (int)$adjustment;
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
        $this->balance = (int)$balance;
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
