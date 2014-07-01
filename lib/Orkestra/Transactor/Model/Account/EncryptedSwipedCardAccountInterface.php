<?php
namespace Orkestra\Transactor\Model\Account;

/**
 * An encrypted swiped credit card
 *
 * The differentiation is simply to easily know if the data held on the card is encrypted
 */
interface EncryptedSwipedCardAccountInterface extends SwipedCardAccountInterface
{
    /**
     * @param string $format
     */
    public function setFormat($format);

    /**
     * @return string
     */
    public function getFormat();

    /**
     * @param string $keySerialNumber
     */
    public function setKeySerialNumber($keySerialNumber);

    /**
     * @return string
     */
    public function getKeySerialNumber();
}