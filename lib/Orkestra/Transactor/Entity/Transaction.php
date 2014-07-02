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
use Orkestra\Transactor\Model\AccountInterface;
use Orkestra\Transactor\Model\CredentialsInterface;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\ResultInterface;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * Transaction Entity
 *
 * Represents a single transaction
 *
 * @ORM\Table(name="orkestra_transactions")
 * @ORM\Entity
 */
class Transaction extends AbstractEntity implements TransactionInterface
{
    /**
     * @var int $amount
     *
     * @ORM\Column(name="amount", type="integer")
     */
    protected $amount;

    /**
     * @var TransactionType $type
     *
     * @ORM\Column(name="type", type="enum.orkestra.transaction_type")
     */
    protected $type;

    /**
     * @var NetworkType $network
     *
     * @ORM\Column(name="network", type="enum.orkestra.network_type")
     */
    protected $network;

    /**
     * @var ResultStatus
     *
     * @ORM\Column(name="status", type="enum.orkestra.result_status")
     */
    protected $status;

    /**
     * @var TransactionInterface $parent
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Model\TransactionInterface", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     *
     * @ORM\OneToMany(targetEntity="Orkestra\Transactor\Model\TransactionInterface", mappedBy="parent", cascade={"persist"})
     */
    protected $children;

    /**
     * @var AccountInterface $account
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Model\AccountInterface", inversedBy="transactions", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * })
     */
    protected $account;

    /**
     * @var CredentialsInterface $credentials
     *
     * @ORM\ManyToOne(targetEntity="Orkestra\Transactor\Model\CredentialsInterface")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="credentials_id", referencedColumnName="id")
     * })
     */
    protected $credentials;

    /**
     * @var ResultInterface $result
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Model\ResultInterface", mappedBy="transaction", cascade={"persist"})
     */
    protected $result;

    /**
     * Constructor
     *
     * @param TransactionInterface|null $parent
     */
    public function __construct(TransactionInterface $parent = null)
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
     * @param int $amount
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
     * @return int
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
     * @param TransactionType $type
     */
    public function setType(TransactionType $type)
    {
        if ($this->isTransacted()) {
            return;
        }

        $this->type = $type;
    }

    /**
     * Gets the transaction type
     *
     * @return TransactionType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the network
     *
     * @param NetworkType $network
     */
    public function setNetwork(NetworkType $network)
    {
        if ($this->isTransacted() || $this->parent) {
            return;
        }

        $this->network = $network;
    }

    /**
     * Gets the network
     *
     * @return NetworkType
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Sets the status
     *
     * @param ResultStatus $status
     */
    public function setStatus(ResultStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Gets the status
     *
     * @return ResultStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the parent transaction
     *
     * @return TransactionInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Creates a new child transaction
     *
     * @param TransactionType $type
     * @param int|null        $amount
     *
     * @return TransactionInterface
     */
    public function createChild(TransactionType $type, $amount = null)
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
     * @return TransactionInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the associated account
     *
     * @param AccountInterface $account
     */
    public function setAccount(AccountInterface $account)
    {
        if ($this->isTransacted() || $this->parent) {
            return;
        }

        $this->account = $account;
    }

    /**
     * Gets the associated account
     *
     * @return AccountInterface
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Gets the associated result
     *
     * @return ResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the associated credentials
     *
     * @param CredentialsInterface $credentials
     */
    public function setCredentials(CredentialsInterface $credentials)
    {
        if ($this->isTransacted() || $this->parent) {
            return;
        }

        $this->credentials = $credentials;
    }

    /**
     * Gets the associated credentials
     *
     * @return CredentialsInterface
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

        return $this->children->exists(function ($key, TransactionInterface $child) {
            return $child->getType() == TransactionType::REFUND && $child->getStatus() == ResultStatus::APPROVED;
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
            return $this->parent->isVoided();
        }

        return $this->children->exists(function ($key, TransactionInterface $child) {
            return $child->getType() == TransactionType::VOID && $child->getStatus() == ResultStatus::APPROVED;
        });
    }
}
