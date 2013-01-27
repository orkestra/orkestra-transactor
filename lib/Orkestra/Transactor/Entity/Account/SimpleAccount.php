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

/**
 * Represents any account, used for Cash or Check transactors
 *
 * @ORM\Entity
 */
class SimpleAccount extends AbstractAccount
{
    protected $accountNumber = '';

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = (string) $accountNumber;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Cash or Check';
    }
}
