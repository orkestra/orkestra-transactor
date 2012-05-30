<?php

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
     * @var string
     *
     * @ORM\Column(name="card_address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="card_city", type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="card_region", type="string", length=3, nullable=true)
     */
    protected $region;

    /**
     * @var string
     *
     * @ORM\Column(name="card_country", type="string", length=3, nullable=true)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="card_postal_code", type="string", nullable=true)
     */
    protected $postalCode;

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
     * Sets the address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Gets the address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Gets the city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the postal code
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Gets the postal code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Sets the region
     *
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * Gets the region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Sets the country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Gets the country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
