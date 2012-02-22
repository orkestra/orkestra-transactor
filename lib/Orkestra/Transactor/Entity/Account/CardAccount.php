<?php

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM;

use Orkestra\Transactor\Entity\AccountBase,
    Orkestra\Transactor\Type\Month,
    Orkestra\Transactor\Type\Year;

/**
 * Card Account Entity
 *
 * Represents a single credit card account
 *
 * @ORM\Entity
 * @package Orkestra
 * @subpackage Transactor
 */
class CardAccount extends AccountBase
{
    /**
     * @var Orkestra\Transactor\Type\Month
     * @ORM\Column(name="card_exp_month", type="orkestra.month", length=2, nullable=true)
     */
    protected $expMonth;
    
    /**
     * @var Orkestra\Transactor\Type\Year
     * @ORM\Column(name="card_exp_year", type="orkestra.year", length=4, nullable=true)
     */
    protected $expYear;
    
    /**
     * @var string
     * @ORM\Column(name="card_cvv", type="string", length=4, nullable=true)
     */
    protected $cvv;
    
    /**
     * @var string
     * @ORM\Column(name="card_address", type="string", nullable=true)
     */
    protected $address;
    
    /**
     * @var string
     * @ORM\Column(name="card_city", type="string", nullable=true)
     */
    protected $city;
    
    /**
     * @var string
     * @ORM\Column(name="card_region", type="string", length=3, nullable=true)
     */
    protected $region;
    
    /**
     * @var string
     * @ORM\Column(name="card_country", type="string", length=3, nullable=true)
     */
    protected $country;
    
    /**
     * @var string
     * @ORM\Column(name="card_postal_code", type="string", nullable=true)
     */
    protected $postalCode;
    
    /**
     * Set Exp Month
     *
     * @param Orkestra\Transactor\Type\Month $expMonth
     */
    public function setExpMonth(Month $expMonth)
    {
        $this->expMonth = $expMonth;
    }
    
    /**
     * Get Exp Month
     *
     * @return Orkestra\Transactor\Type\Month
     */
    public function getExpMonth()
    {
        return $this->expMonth;
    }
    
    /**
     * Set Exp Year
     *
     * @param Orkestra\Transactor\Type\Year $expYear
     */
    public function setExpYear(Year $expYear)
    {
        $this->expYear = $expYear;
    }
    
    /**
     * Get Exp Year
     *
     * @return Orkestra\Transactor\Type\Year
     */
    public function getExpYear()
    {
        return $this->expYear;
    }
    
    /**
     * Set Cvv
     *
     * @param string $cvv
     */
    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    }
    
    /**
     * Get Cvv
     *
     * @return string
     */
    public function getCvv()
    {
        return $this->cvv;
    }
}