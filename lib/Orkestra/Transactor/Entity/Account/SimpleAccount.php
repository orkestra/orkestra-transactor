<?php

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Entity\Account\BankAccount\AccountType;

/**
 * Represents any account, used for Cash or Check transactors
 *
 * @ORM\Entity
 */
class SimpleAccount extends AbstractAccount
{

}
