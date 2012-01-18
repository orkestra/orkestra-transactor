<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM,
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
    const TYPE_CARD_SALE = 'card.sale';
    const TYPE_CARD_AUTH = 'card.auth';
    const TYPE_ACH_REQUEST = 'ach.request';
    const TYPE_ACH_RESPONSE = 'ach.response';
    const TYPE_MFA_TRANSFER = 'mfa.transfer';
    
    /**
     * @var array
     */
    protected static $_types = array(
        self::TYPE_CARD_SALE,
        self::TYPE_CARD_AUTH,
        self::TYPE_ACH_REQUEST,
        self::TYPE_ACH_RESPONSE,
        self::TYPE_MFA_TRANSFER
    );
    
    /**
     * Get Types
     *
     * Returns an array of available transaction types
     *
     * @return array Array of available transaction types
     */
    public static function getTypes()
    {
        return static::$_types;
    }
    
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
     * @var Orkestra\Transactor\Entity\AccountBase $account
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entity\AccountBase", mappedBy="transaction")
     */
    protected $account;
     
	/**
     * @var Orkestra\Transactor\Entity\TransactionResult $result
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entity\TransactionResultBase", mappedBy="transaction")
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
     * Set Type
     *
     * @param string $type A valid transaction type
     */
    public function setType($type)
    {
        if (!in_array($type, static::$_types)) {
            throw new \InvalidArgumentException('Invalid type specified.');
        }
        
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
     * Set Account
     *
     * @param Orkestra\Transactor\Entity\AccountBase $account
     */
    public function setAccount(AccountBase $account)
    {
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