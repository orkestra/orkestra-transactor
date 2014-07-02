<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Model\Account;

/**
 * A swiped credit card
 */
interface SwipedCardAccountInterface extends CardAccountInterface
{
    /**
     * @param string $trackOne
     */
    public function setTrackOne($trackOne);

    /**
     * @return string
     */
    public function getTrackOne();

    /**
     * @param string $trackTwo
     */
    public function setTrackTwo($trackTwo);

    /**
     * @return string
     */
    public function getTrackTwo();

    /**
     * @param string $trackThree
     */
    public function setTrackThree($trackThree);

    /**
     * @return string
     */
    public function getTrackThree();
}