<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Entity;

use Doctrine\ORM\Mapping as ORM;
use Orkestra\Common\Entity\AbstractEntity;

/**
 * Transaction Entity
 *
 * Represents a single transaction
 *
 * @ORM\Table(name="orkestra_transactions")
 * @ORM\Entity
 */
class Transaction extends AbstractEntity
{
    /**
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    protected $amount;

    /**
     * @var \Orkestra\Transactor\Entity\Transaction\TransactionType $type
     *
     * @ORM\Column(name="type", type="enum.orkestra.transaction_type")
     */
    protected $type;

    /**
     * @var \Orkestra\Transactor\Entity\Transaction\NetworkType $network
     *
     * @ORM\Column(name="network", type="enum.orkestra.network_type")
     */
    protected $network;

    /**
     * @var \Orkestra\Transactor\Entity\Result\ResultStatus
     *
     * @ORM\Column(name="status", type="enum.orkestra.result_status")
     */
    protected $status;

    /**
     * @var \Orkestra\Transactor\Entity\Transaction $parent
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\Transaction", inversedBy="children", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     *
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Entity\Transaction", mappedBy="parent", cascade={"persist"}, fetch="EAGER")
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
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Entity\Credentials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="credentials_id", referencedColumnName="id")
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
     *
     * @param \Orkestra\Transactor\Entity\Transaction|null $parent
     */
    public function __construct(Transaction $parent = null)
    {
        if ($parent) {
            $this->parent = $parent;
            $this->network = $parent->getNetwork();
            $this->credentials = $parent->getCredentials();
            $this->account = $parent->getAccount();
        }

        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->result = new Result($this);
    }

    /**
     * Returns true if this transaction is a parent transaction
     *
     * @return boolean
     */
    public function isParent()
    {
        return !$this->parent ? true : false;
    }

    /**
     * Sets the amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        if ($this->isTransacted()) {
            return;
        }

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
        return $this->isTransacted();
    }

    /**
     * Returns true if this transaction has been transacted
     *
     * @return boolean
     */
    public function isTransacted()
    {
        return $this->result->isTransacted();
    }

    /**
     * Sets the transaction type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType $type
     */
    public function setType(Transaction\TransactionType $type)
    {
        if ($this->isTransacted()) {
            return;
        }

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
     * Sets the network
     *
     * @param \Orkestra\Transactor\Entity\Transaction\NetworkType $network
     */
    public function setNetwork(Transaction\NetworkType $network)
    {
        if ($this->isTransacted() || $this->parent) {
            return;
        }

        $this->network = $network;
    }

    /**
     * Gets the network
     *
     * @return \Orkestra\Transactor\Entity\Transaction\NetworkType
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Sets the status
     *
     * @param \Orkestra\Transactor\Entity\Result\ResultStatus $status
     */
    public function setStatus(Result\ResultStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Gets the status
     *
     * @return \Orkestra\Transactor\Entity\Result\ResultStatus
     */
    public function getStatus()
    {
        return $this->status;
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
     * @param float                                                   $amount
     *
     * @return \Orkestra\Transactor\Entity\Transaction
     */
    public function createChild(Transaction\TransactionType $type, $amount = null)
    {
        $child = new Transaction($this);
        $child->setType($type);
        $child->setAmount($amount ?: $this->amount);
        $this->children->add($child);

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
        if ($this->isTransacted() || $this->parent) {
            return;
        }

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
        if ($this->isTransacted() || $this->parent) {
            return;
        }

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

    /**
     * Returns true if the transaction has been refunded
     *
     * @return bool
     */
    public function isRefunded()
    {
        if ($this->parent) {
            return $this->parent->isRefunded();
        }

        return $this->children->exists(function($key, Transaction $child) {
            return $child->getType() == Transaction\TransactionType::REFUND && $child->getStatus() == Result\ResultStatus::APPROVED;
        });
    }

    /**
     * Returns true if the transaction has been voided
     *
     * @return bool
     */
    public function isVoided()
    {
        if ($this->parent) {
            return $this->parent->isRefunded();
        }

        return $this->children->exists(function($key, Transaction $child) {
            return $child->getType() == Transaction\TransactionType::VOID && $child->getStatus() == Result\ResultStatus::APPROVED;
        });
    }
}
