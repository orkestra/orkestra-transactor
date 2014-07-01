<?php
namespace Orkestra\Transactor\Model;

use Orkestra\Transactor\Entity\Result;

/**
 * A transaction result
 */
interface ResultInterface
{
    /**
     * Is Active
     *
     * @return boolean
     */
    public function isActive();

    /**
     * Returns true if the transaction has been transacted
     *
     * @return bool
     */
    public function isTransacted();

    /**
     * Sets the associated Transaction
     *
     * @param TransactionInterface $transaction
     */
    public function setTransaction(TransactionInterface $transaction);

    /**
     * Gets the associated Transaction
     *
     * @return TransactionInterface
     */
    public function getTransaction();

    /**
     * Set External ID
     *
     * @param string $externalId
     */
    public function setExternalId($externalId);

    /**
     * Get External ID
     *
     * @return string
     */
    public function getExternalId();

    /**
     * Set Message
     *
     * @param string $message
     */
    public function setMessage($message);

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set Data
     *
     * @param string $key The key of which data to set
     * @param mixed  $value
     */
    public function setData($key, $value);

    /**
     * Get Data
     *
     * @param  string $key The key of which data to get
     *
     * @return mixed
     */
    public function getData($key);

    /**
     * Sets the result type
     *
     * @param \Orkestra\Transactor\Entity\Result\ResultStatus $status
     */
    public function setStatus(Result\ResultStatus $status);

    /**
     * Gets the result type
     *
     * @return \Orkestra\Transactor\Entity\Result\ResultStatus
     */
    public function getStatus();

    /**
     * Gets the transactor
     *
     * @return string
     */
    public function getTransactor();

    /**
     * Sets the transactor
     *
     * @param \Orkestra\Transactor\TransactorInterface|string $transactor
     */
    public function setTransactor($transactor);

    /**
     * @return \DateTime
     */
    public function getDateTransacted();
}