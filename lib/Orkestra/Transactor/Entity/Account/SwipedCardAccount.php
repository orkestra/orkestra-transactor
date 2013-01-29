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
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

/**
 * A swiped credit card
 *
 * @ORM\Entity
 */
class SwipedCardAccount extends CardAccount
{
    /**
     * @var string
     *
     * @ORM\Column(name="track_one", type="string")
     */
    protected $trackOne;

    /**
     * @var string
     *
     * @ORM\Column(name="track_two", type="string")
     */
    protected $trackTwo;

    /**
     * @var string
     *
     * @ORM\Column(name="track_three", type="string")
     */
    protected $trackThree;

    /**
     * @param string $trackOne
     */
    public function setTrackOne($trackOne)
    {
        $this->trackOne = $trackOne;
        $this->updateInformationFromStripeData();
    }

    /**
     * @return string
     */
    public function getTrackOne()
    {
        return $this->trackOne;
    }

    /**
     * @param string $trackThree
     */
    public function setTrackThree($trackThree)
    {
        $this->trackThree = $trackThree;
        $this->updateInformationFromStripeData();
    }

    /**
     * @return string
     */
    public function getTrackThree()
    {
        return $this->trackThree;
    }

    /**
     * @param string $trackTwo
     */
    public function setTrackTwo($trackTwo)
    {
        $this->trackTwo = $trackTwo;
        $this->updateInformationFromStripeData();
    }

    /**
     * @return string
     */
    public function getTrackTwo()
    {
        return $this->trackTwo;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Swiped Credit Card';
    }

    /**
     * Updates the Card's account information using the track data
     */
    private function updateInformationFromStripeData()
    {
        if ($this->trackOne) {
            $parts = explode('^', $this->trackOne);

            $this->setAccountNumber(substr($parts[0], 2));
            $this->setExpMonth(new Month(substr($parts[2], 2, 2)));
            $this->setExpYear(new Year('20' . substr($parts[2], 0, 2)));
        } elseif ($this->trackTwo) {
            $parts = explode('=', $this->trackTwo);

            $this->setAccountNumber(substr($parts[0], 1));
            $this->setExpMonth(new Month(substr($parts[1], 2, 2)));
            $this->setExpYear(new Year('20' . substr($parts[1], 0, 2)));
        } elseif ($this->trackThree) {
            $parts = explode('=', $this->trackThree);

            $this->setAccountNumber(substr($parts[0], 3));
            $this->setExpMonth(new Month(substr($parts[1], -3, 2)));
            $this->setExpYear(new Year('20' . substr($parts[1], -5, 2)));
        }
    }
}
