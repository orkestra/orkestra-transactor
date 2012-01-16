<?php

namespace Orkestra\Transactor\Entities;

use \Doctrine\ORM\Mapping as ORM,
    \DateTime;

/**
 * Transaction Result Base
 *
 * Base class for all Transaction Results
 *
 * @ORM\Table(name="orkestra_transaction_results")
 * @ORM\Entity
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *   "ApprovedResult" = "Orkestra\Transactor\Entities\TransactorResult\ApprovedResult"
 * })
 * @package Orkestra
 * @subpackage Transactor
 */
abstract class TransactionResultBase extends EntityBase
{
    /**
     * @var Orkestra\Transactor\Entities\Transaction
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entities\Transaction", inversedBy="result")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    protected $transaction;
    
    /**
     * @var Orkestra\Transactor\Entities\Transactor
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entities\Transactor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transactor_id", referencedColumnName="id")
     * })
     */
    protected $transactor;
}