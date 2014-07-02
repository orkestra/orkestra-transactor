<?php
namespace Orkestra\Transactor\Model;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;

/**
 * A single transaction
 */
interface TransactionInterface
{
    /**
     * Returns true if this transaction is a parent transaction
     *
     * @return boolean
     */
    public function isParent();

    /**
     * Sets the amount, in cents
     *
     * @param int $amount
     */
    public function setAmount($amount);

    /**
     * Gets the amount, in cents
     *
     * @return int
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
     * @param TransactionType $type
     */
    public function setType(TransactionType $type);

    /**
     * Gets the transaction type
     *
     * @return TransactionType
     */
    public function getType();

    /**
     * Sets the network
     *
     * @param NetworkType $network
     */
    public function setNetwork(NetworkType $network);

    /**
     * Gets the network
     *
     * @return NetworkType
     */
    public function getNetwork();

    /**
     * Sets the status
     *
     * @param ResultStatus $status
     */
    public function setStatus(ResultStatus $status);

    /**
     * Gets the status
     *
     * @return ResultStatus
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
     * @param TransactionType $type
     * @param int|null        $amount The child transaction amount, in cents
     *
     * @return TransactionInterface
     */
    public function createChild(TransactionType $type, $amount = null);

    /**
     * Gets the transaction's children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren();

    /**
     * Sets the associated account
     *
     * @param AccountInterface $account
     */
    public function setAccount(AccountInterface $account);

    /**
     * Gets the associated account
     *
     * @return AccountInterface
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