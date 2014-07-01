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
use Orkestra\Transactor\Model\Account\SwipedCardAccountInterface;

/**
 * A swiped credit card
 *
 * @ORM\Entity
 */
class SwipedCardAccount extends CardAccount implements SwipedCardAccountInterface
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
}
