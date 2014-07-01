<?php
namespace Orkestra\Transactor\Model\Account;

/**
 * A points account
 */
interface PointsAccountInterface
{
    /**
     * Adds $adjustment to the balance
     *
     * @param int $adjustment
     */
    public function adjustBalance($adjustment);

    /**
     * Gets the account balance
     *
     * @return int
     */
    public function getBalance();

    /**
     * Sets the account balance
     *
     * @param int $balance
     */
    public function setBalance($balance);
}