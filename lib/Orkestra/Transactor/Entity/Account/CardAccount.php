<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

/**
 * Represents a single credit card account
 *
 * @ORM\Entity
 */
class CardAccount extends AbstractAccount
{
    /**
     * @var \Orkestra\Transactor\Type\Month
     *
     * @ORM\Column(name="card_exp_month", type="orkestra.month", length=2, nullable=true)
     */
    protected $expMonth;

    /**
     * @var \Orkestra\Transactor\Type\Year
     * @ORM\Column(name="card_exp_year", type="orkestra.year", length=4, nullable=true)
     */
    protected $expYear;

    /**
     * @var string
     *
     * @ORM\Column(name="card_cvv", type="string", length=4, nullable=true)
     */
    protected $cvv;

    /**
     * Sets the expiration month
     *
     * @param \Orkestra\Transactor\Type\Month $expMonth
     */
    public function setExpMonth(Month $expMonth)
    {
        $this->expMonth = $expMonth;
    }

    /**
     * Gets the expiration month
     *
     * @return \Orkestra\Transactor\Type\Month
     */
    public function getExpMonth()
    {
        return $this->expMonth;
    }

    /**
     * Sets the expiration year
     *
     * @param \Orkestra\Transactor\Type\Year $expYear
     */
    public function setExpYear(Year $expYear)
    {
        $this->expYear = $expYear;
    }

    /**
     * Gets the expiration year
     *
     * @return \Orkestra\Transactor\Type\Year
     */
    public function getExpYear()
    {
        return $this->expYear;
    }

    /**
     * Sets the security code
     *
     * @param string $cvv
     */
    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    /**
     * Gets the security code
     *
     * @return string
     */
    public function getCvv()
    {
        return $this->cvv;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Credit Card';
    }
}
