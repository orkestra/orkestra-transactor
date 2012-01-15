<?php

namespace Orkestra\Transactor\Entities;

use \Doctrine\ORM\Mapping as ORM,
    \DateTime;

/**
 * Transaction Entity
 *
 * Represents a single transaction
 *
 * @ORM\Table(name="orkestra_transactions", indexes={@ORM\Index(name="IX_transaction_date", columns={"date_posted"})})
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
class Transaction extends EntityBase
{
    /**
     * @var decimal $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    protected $amount;

    /**
     * @var boolean $transacted
     *
     * @ORM\Column(name="transacted", type="boolean")
     */
    protected $transacted = false;

    /**
     * @var DateTime $dateTransacted
     *
     * @ORM\Column(name="date_transacted", type="datetime")
     */
    protected $dateTransacted;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description = '';

    /**
     * @var Orkestra\Transactor\TransactionType
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\TransactionType", inversedBy="transaction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * })
     */
    protected $type;

	/**
     * @var Orkestra\Transactor\TransactionResult
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\TransactionResult", inversedBy="transaction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="result_id", referencedColumnName="id")
     * })
     */
    protected $result;
    
    /**
     * Set Amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    
    /**
     * Get Amount
     *
     * @return decimal
     */
    public function getAmount()
    {
        return $this->amount;
    }
        
    /**
     * Set Transacted
     *
     * @param boolean $transacted
     */
    public function setTransacted($transacted)
    {
        $this->transacted = $transacted ? true : false;
    }
    
    /**
     * Get Transacted
     *
     * @return boolean
     */
    public function getTransacted()
    {
        return $this->transacted;
    }
    
    /**
     * Is Transacted
     *
     * @return boolean
     */
    public function isTransacted()
    {
        return $this->getTransacted();
    }
    
    /**
     * Set Date Transacted
     *
     * @param DateTime $dateTransacted
     */
    public function setDateTransacted(DateTime $dateTransacted)
    {
        $this->dateTransacted = $dateTransacted;
    }
    
    /**
     * Get Date Transacted
     *
     * @return DateTime
     */
    public function getDateTransacted()
    {
        return $this->dateTransacted;
    }
    
    /**
     * Set Description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set Type
     *
     * @param Orkestra\Transactor\TransactionType $type
     */
    public function setType(TransactionTypeBase $type)
    {
        $this->type = $type;
    }

    /**
     * Get Type
     *
     * @return Orkestra\Transactor\TransactionType
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set Result
     *
     * @param Orkestra\Transactor\TransactionResult $result
     */
    public function setResult(TransactionResultBase $result)
    {
        $this->result = $result;
    }

    /**
     * Get Type
     *
     * @return Orkestra\Transactor\TransactionResult
     */
    public function getResult()
    {
        return $this->result;
    }
}