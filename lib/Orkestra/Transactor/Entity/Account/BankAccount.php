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
use Orkestra\Transactor\Entity\Account\BankAccount\AccountType;
use Orkestra\Transactor\Model\Account\BankAccountInterface;

/**
 * Represents a Bank Account
 *
 * @ORM\Entity
 */
class BankAccount extends AbstractAccount implements BankAccountInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="ach_routing_number", type="string", nullable=true)
     */
    protected $routingNumber;

    /**
     * @var Orkestra\Transactor\Entity\Account\BankAccount\AccountType
     *
     * @ORM\Column(name="account_type", type="enum.orkestra.bank_account_type", nullable=true)
     */
    protected $accountType;

    /**
     * Gets the routing number
     *
     * @return string $routingNumber
     */
    public function getRoutingNumber()
    {
        return $this->routingNumber;
    }

    /**
     * Sets the routing number
     *
     * @param string $routingNumber
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;
    }

    /**
     * Gets the account type
     *
     * @return Orkestra\Transactor\Entity\Account\BankAccount\AccountType $accountType
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * Sets the account type
     *
     * @param Orkestra\Transactor\Entity\Account\BankAccount\AccountType $accountType
     */
    public function setAccountType(AccountType $accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Bank Account';
    }
}
