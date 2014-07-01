<?php
namespace Orkestra\Transactor\Model;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Transaction;

/**
 * A single transaction
 */
interface TransactionInterface
{
    /**
     * Is Active
     *
     * @return boolean
     */
    public function isActive();

    /**
     * Returns true if this transaction is a parent transaction
     *
     * @return boolean
     */
    public function isParent();

    /**
     * Sets the amount
     *
     * @param float $amount
     */
    public function setAmount($amount);

    /**
     * Gets the amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Returns true if this transaction has been transacted
     *
     * @return boolean
     */
    public function isTransacted();

    /**
     * Sets the transaction type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType $type
     */
    public function setType(Transaction\TransactionType $type);

    /**
     * Gets the transaction type
     *
     * @return \Orkestra\Transactor\Entity\Transaction\TransactionType
     */
    public function getType();

    /**
     * Sets the network
     *
     * @param \Orkestra\Transactor\Entity\Transaction\NetworkType $network
     */
    public function setNetwork(Transaction\NetworkType $network);

    /**
     * Gets the network
     *
     * @return \Orkestra\Transactor\Entity\Transaction\NetworkType
     */
    public function getNetwork();

    /**
     * Sets the status
     *
     * @param \Orkestra\Transactor\Entity\Result\ResultStatus $status
     */
    public function setStatus(Result\ResultStatus $status);

    /**
     * Gets the status
     *
     * @return \Orkestra\Transactor\Entity\Result\ResultStatus
     */
    public function getStatus();

    /**
     * Gets the parent transaction
     *
     * @return TransactionInterface
     */
    public function getParent();

    /**
     * Creates a new child transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction\TransactionType $type
     * @param float                                                   $amount
     *
     * @return TransactionInterface
     */
    public function createChild(Transaction\TransactionType $type, $amount = null);

    /**
     * Gets the transaction's children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren();

    /**
     * Sets the associated account
     *
     * @param \Orkestra\Transactor\Entity\AbstractAccount $account
     */
    public function setAccount(AbstractAccount $account);

    /**
     * Gets the associated account
     *
     * @return \Orkestra\Transactor\Entity\AbstractAccount
     */
    public function getAccount();

    /**
     * Gets the associated result
     *
     * @return ResultInterface
     */
    public function getResult();

    /**
     * Sets the associated credentials
     *
     * @param CredentialsInterface $credentials
     */
    public function setCredentials(CredentialsInterface $credentials);

    /**
     * Gets the associated credentials
     *
     * @return CredentialsInterface
     */
    public function getCredentials();

    /**
     * Returns true if the transaction has been refunded
     *
     * @return bool
     */
    public function isRefunded();

    /**
     * Returns true if the transaction has been voided
     *
     * @return bool
     */
    public function isVoided();
}