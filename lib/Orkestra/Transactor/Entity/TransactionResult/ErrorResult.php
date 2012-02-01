<?php

namespace Orkestra\Transactor\Entity\TransactionResult;

use Orkestra\Transactor\Entity\TransactionResultBase;

use Doctrine\ORM\Mapping as ORM;

/**
 * Error Result
 *
 * Represents an error that occurred while processing a transaction
 *
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
class ErrorResult extends TransactionResultBase
{
    
}