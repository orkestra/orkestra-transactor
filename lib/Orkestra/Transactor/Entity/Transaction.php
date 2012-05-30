<?php

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM;
use Orkestra\Common\Entity\EntityBase;
use Orkestra\Common\Type\DateTime;

use Orkestra\Transactor\Exception\TransactorException;

/**
 * Transaction Entity
 *
 * Represents a single transaction
 *
 * @ORM\Table(name="orkestra_transactions", indexes={@ORM\Index(name="IX_date_transacted", columns={"date_transacted"})})
 * @ORM\Entity
 */
class Transaction extends EntityBase
{
    /**
     * @var float $amount
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
     * @var string $type
     *
     * @ORM\Column(name="type", type="enum.orkestra.transaction_type")
     */
    protected $type;

    /**
     * @var \Orkestra\Transactor\Entity\Transaction $parent
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\Transaction", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     *
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Entity\Transaction", mappedBy="parent", cascade={"persist"})
     */
    protected $children;

    /**
     * @var \Orkestra\Transactor\Entity\AbstractAccount $account
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\AbstractAccount", inversedBy="transactions", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * })
     */
    protected $account;

    /**
     * @var \Orkestra\Transactor\Entity\Credentials $credentials
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\AbstractAccount", inversedBy="transactions", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * })
     */
    protected $credentials;

	/**
     * @var \Orkestra\Transactor\Entity\Result $result
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Entity\Result", mappedBy="transaction", cascade={"persist"})
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
     * Sets the amount
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
     * Gets the amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Returns true if this transaction has been transacted
     *
     * @return boolean
     */
    public function getTransacted()
    {
        return $this->transacted;
    }

    /**
     * Returns true if this transaction has been transacted
     *
     * @return boolean
     */
    public function isTransacted()
    {
        return $this->getTransacted();
    }

    /**
     * Gets the date transacted
     *
     * @return DateTime
     */
    public function getDateTransacted()
    {
        return $this->dateTransacted;
    }

    /**
     * Sets the transaction type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType $type
     */
    public function setType(Transaction\TransactionType $type)
    {
        if ($this->transacted)
            return;

        $this->type = $type;
    }

    /**
     * Gets the transaction type
     *
     * @return \Orkestra\Transactor\Entity\Transaction\TransactionType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the parent transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $parent
     */
    public function setParent(Transaction $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Gets the parent transaction
     *
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Creates a new child transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType $type
     * @param float $amount
     *
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    public function createChild(Transaction\TransactionType $type, $amount = 0.0)
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
     * Gets the transaction's children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the associated account
     *
     * @param \Orkestra\Transactor\Entity\AbstractAccount $account
     */
    public function setAccount(AbstractAccount $account)
    {
        if ($this->transacted)
            return;

        $this->account = $account;
    }

    /**
     * Gets the associated account
     *
     * @return \Orkestra\Transactor\Entity\AbstractAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Sets the associated result
     *
     * @param \Orkestra\Transactor\Entity\Result $result
     */
    public function setResult(Result $result)
    {
        if ($this->transacted)
            return;

        $this->transacted = true;
        $this->dateTransacted = new DateTime();
        $this->result = $result;
    }

    /**
     * Gets the associated result
     *
     * @return \Orkestra\Transactor\Entity\Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the associated credentials
     *
     * @param \Orkestra\Transactor\Entity\Credentials $credentials
     */
    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Gets the associated credentials
     *
     * @return \Orkestra\Transactor\Entity\Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
}
