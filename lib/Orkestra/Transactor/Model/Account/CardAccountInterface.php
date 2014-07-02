<?php

namespace Orkestra\Transactor\Model\Account;

use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

/**
 * A credit card account
 */
interface CardAccountInterface
{
    /**
     * Set Account Number
     *
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber);

    /**
     * Get Account Number
     *
     * @return string
     */
    public function getAccountNumber();

    /**
     * Sets the expiration month
     *
     * @param \Orkestra\Transactor\Type\Month $expMonth
     */
    public function setExpMonth(Month $expMonth);

    /**
     * Gets the expiration month
     *
     * @return \Orkestra\Transactor\Type\Month
     */
    public function getExpMonth();

    /**
     * Sets the expiration year
     *
     * @param \Orkestra\Transactor\Type\Year $expYear
     */
    public function setExpYear(Year $expYear);

    /**
     * Gets the expiration year
     *
     * @return \Orkestra\Transactor\Type\Year
     */
    public function getExpYear();

    /**
     * Sets the security code
     *
     * @param string $cvv
     */
    public function setCvv($cvv);

    /**
     * Gets the security code
     *
     * @return string
     */
    public function getCvv();
}