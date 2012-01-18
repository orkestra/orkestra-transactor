<?php

namespace Orkestra\Transactor\Entity\Account;

use Doctrine\ORM\Mapping as ORM,
    Orkestra\Transactor\Entity\AccountBase,
    \DateTime;

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
     * @var string
     * @ORM\Column(name="card_exp_month", type="string", length=2, nullable=true)
     */
    protected $expMonth;
    
    /**
     * @var string
     * @ORM\Column(name="card_exp_year", type="string", length=4, nullable=true)
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
}