<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM,
    Orkestra\Common\Entity\EntityBase,
    Orkestra\Common\Type\DateTime;
    
use Orkestra\Transactor\Exception\TransactException;

/**
 * Transaction Entity
 *
 * Represents a single transaction
 *
 * @ORM\Table(name="orkestra_transactions", indexes={@ORM\Index(name="IX_date_transacted", columns={"date_transacted"})})
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
class Transaction extends EntityBase
{
    /**
     * @var decimal $amount
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    protected $amount;
    
    /**
     * @var boolean $transacted
     * @ORM\Column(name="transacted", type="boolean")
     */
    protected $transacted = false;

    /**
     * @var DateTime $dateTransacted
     * @ORM\Column(name="date_transacted", type="datetime")
     */
    protected $dateTransacted;

    /**
     * @var string $type
     * @ORM\Column(name="type", type="string")
     */
    protected $type;
    
    /**
     * @var Orkestra\Transactor\Entity\Transaction $parent
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\Transaction", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;
    
    /**
     * @var Doctrine\Common\Collections\Collection $children
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Entity\Transaction", mappedBy="parent", cascade={"persist"})
     */
    protected $children;
    
    /**
     * @var Orkestra\Transactor\Entity\AccountBase $account
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\AccountBase", inversedBy="transactions", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * })
     */
    protected $account;
     
	/**
     * @var Orkestra\Transactor\Entity\TransactionResult $result
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entity\TransactionResultBase", mappedBy="transaction", cascade={"persist"})
     */
    protected $result;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set Amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        if ($this->transacted)
            return;
        
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
     * Get Date Transacted
     *
     * @return DateTime
     */
    public function getDateTransacted()
    {
        return $this->dateTransacted;
    }

    /**
     * Set Type
     *
     * @param string $type A valid transaction type
     */
    public function setType(TransactionType $type)
    {
        if ($this->transacted)
            return;

        $this->type = $type;
    }

    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set Parent
     *
     * @param Orkestra\Transactor\Entity\Transaction $parent
     */
    public function setParent(Transaction $parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * Get Parent
     *
     * @return Orkestra\Transactor\Entity\Transaction
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Create Child
     *
     * @return Orkestra\Transactor\Entity\Transaction
     */
    public function createChild(TransactionType $type, $amount = 0)
    {
        $child = new Transaction();
        $child->setType($type);
        $child->setAmount($amount);
        $child->setAccount($this->account);
        $child->setParent($this);
        $this->children[] = $child;
        
        return $child;
    }
    
    /**
     * Get Children
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * Set Account
     *
     * @param Orkestra\Transactor\Entity\AccountBase $account
     */
    public function setAccount(AccountBase $account)
    {
        if ($this->transacted)
            return;
            
        $this->account = $account;
    }
    
    /**
     * Get Account
     *
     * @return Orkestra\Transactor\Entity\AccountBase
     */
    public function getAccount()
    {
        return $this->account;
    }
    
    /**
     * Set Result
     *
     * @param Orkestra\Transactor\TransactionResultBase $result
     */
    public function setResult(TransactionResultBase $result)
    {
        if ($this->transacted)
            return;
            
        $this->transacted = true;
        $this->dateTransacted = new DateTime();
        $this->result = $result;
    }

    /**
     * Get Type
     *
     * @return Orkestra\Transactor\TransactionResultBase
     */
    public function getResult()
    {
        return $this->result;
    }
}