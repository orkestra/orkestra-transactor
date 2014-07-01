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
use Orkestra\Transactor\Model\Account\EncryptedSwipedCardAccountInterface;

/**
 * An encrypted swiped credit card
 *
 * The differentiation is simply to easily know if the data held on the card is encrypted
 *
 * @ORM\Entity
 */
class EncryptedSwipedCardAccount extends SwipedCardAccount implements EncryptedSwipedCardAccountInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="key_serial_number", type="string")
     */
    protected $keySerialNumber = '';

    /**
     * @var string
     *
     * @ORM\Column(name="encryption_format", type="string")
     */
    protected $format = '';

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $keySerialNumber
     */
    public function setKeySerialNumber($keySerialNumber)
    {
        $this->keySerialNumber = $keySerialNumber;
    }

    /**
     * @return string
     */
    public function getKeySerialNumber()
    {
        return $this->keySerialNumber;
    }

    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType()
    {
        return 'Encrypted Swiped Credit Card';
    }
}
