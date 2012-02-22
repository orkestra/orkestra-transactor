<?php

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM,
    Orkestra\Transactor\Entity\AccountBase;

/**
 * Bank Account Base
 *
 * Base class for all bank accounts
 *
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class BankAccountBase extends AccountBase
{
    /**
     * @var string
     * @ORM\Column(name="ach_routing_number", type="string", nullable=true)
     */
    protected $routingNumber;
}