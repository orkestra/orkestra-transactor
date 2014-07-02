<?php

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