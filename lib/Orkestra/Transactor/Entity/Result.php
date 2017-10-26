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
use Orkestra\Common\Type\DateTime;
use Orkestra\Common\Type\NullDateTime;
use Orkestra\Transactor\Model\ResultInterface;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * Represents the result of a transaction
 *
 * @ORM\Table(name="orkestra_results")
 * @ORM\Entity
 */
class Result extends AbstractEntity implements ResultInterface
{
    /**
     * @var string $externalId
     *
     * @ORM\Column(name="external_id", type="string")
     */
    protected $externalId = '';

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="string")
     */
    protected $message = '';

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="array")
     */
    protected $data = array();

    /**
     * @var \Orkestra\Transactor\Entity\Result\ResultStatus
     *
     * @ORM\Column(name="status", type="enum.orkestra.result_status")
     */
    protected $status;

    /**
     * @var boolean $transacted
     *
     * @ORM\Column(name="transacted", type="boolean")
     */
    protected $transacted = false;

    /**
     * @var \DateTime $dateTransacted
     *
     * @ORM\Column(name="date_transacted", type="datetime", nullable=true)
     */
    protected $dateTransacted;

    /**
     * @var string
     *
     * @ORM\Column(name="transactor", type="string")
     */
    protected $transactor;

    /**
     * @var TransactionInterface
     *
     * @ORM\OneToOne(targetEntity="Orkestra\Transactor\Model\TransactionInterface", inversedBy="result", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    protected $transaction;

    /**
     * Constructor
     *
     * @param TransactionInterface $transaction
     */
    public function __construct(TransactionInterface $transaction)
    {
        $this->transaction = $transaction;
        $this->dateTransacted = new NullDateTime();
        $this->setStatus(new Result\ResultStatus(Result\ResultStatus::UNPROCESSED));
    }

    /**
     * Returns true if the transaction has been transacted
     *
     * @return bool
     */
    public function getTransacted()
    {
        return $this->isTransacted();
    }

    /**
     * Returns true if the transaction has been transacted
     *
     * @return bool
     */
    public function isTransacted()
    {
        return $this->transacted;
    }

    /**
     * Sets the associated Transaction
     *
     * @param TransactionInterface $transaction
     */
    public function setTransaction(TransactionInterface $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Gets the associated Transaction
     *
     * @return TransactionInterface
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set External ID
     *
     * @param string $externalId
     */
    public function setExternalId($externalId)
    {
        $this->externalId = (string) $externalId;
    }

    /**
     * Get External ID
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set Message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set Data
     *
     * @param string $key   The key of which data to set
     * @param mixed  $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get Data
     *
     * @param  string $key The key of which data to get
     * @return mixed
     */
    public function getData($key)
    {
        return empty($this->data[$key]) ? null : $this->data[$key];
    }

    /**
     * Sets the result type
     *
     * @param \Orkestra\Transactor\Entity\Result\ResultStatus $status
     */
    public function setStatus(Result\ResultStatus $status)
    {
        if (Result\ResultStatus::UNPROCESSED !== $status->getValue()) {
            $this->transacted = true;
            $this->dateTransacted = new DateTime();
        }

        if (!$this->transaction->isParent()
            && Result\ResultStatus::ERROR !== $status->getValue()
            && Result\ResultStatus::UNPROCESSED !== $status->getValue())  {
            $this->transaction->getParent()->setStatus($status);
        }

        $this->transaction->setStatus($status);
        $this->status = $status;
    }

    /**
     * Gets the result type
     *
     * @return \Orkestra\Transactor\Entity\Result\ResultStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the transactor
     *
     * @return string
     */
    public function getTransactor()
    {
        return $this->transactor;
    }

    /**
     * Sets the transactor
     *
     * @param \Orkestra\Transactor\TransactorInterface|string $transactor
     */
    public function setTransactor($transactor)
    {
        $this->transactor = is_object($transactor) ? $transactor->getType() : $transactor;
    }

    /**
     * @return \DateTime
     */
    public function getDateTransacted()
    {
        return $this->dateTransacted;
    }
}
