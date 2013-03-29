<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\TrackDecoder;

use Orkestra\Transactor\Entity\Account\EncryptedSwipedCardAccount;
use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\Exception\DecoderException;

/**
 * Supports "Enhanced Encryption" used by IDTech and other swiper manufacturers
 */
class EnhancedEncryptionDecoder implements DecoderInterface
{
    const ENCRYPTION_FORMAT = 'enhanced.encryption';

    /**
     * @var Iso7813Decoder
     */
    private $iso7813Decoder;

    /**
     * Constructor
     *
     * @param Iso7813Decoder $iso7813Decoder
     */
    public function __construct(Iso7813Decoder $iso7813Decoder)
    {
        $this->iso7813Decoder = $iso7813Decoder;
    }

    /**
     * @param $rawTrackData
     *
     * @return bool
     */
    public function supports($rawTrackData)
    {
        return "\x02" === $rawTrackData{0};
    }

    /**
     * Decodes the given raw track data
     *
     * @param \Orkestra\Transactor\Entity\Account\SwipedCardAccount $account
     * @param string                                                $rawTrackData
     *
     * @throws \Orkestra\Transactor\Exception\DecoderException
     */
    public function decode(SwipedCardAccount $account, $rawTrackData)
    {
        if (!$account instanceof EncryptedSwipedCardAccount) {
            throw new DecoderException('Unable to populate account with data. An EncryptedSwipedCardAccount is necessary to decode encrypted track data.');
        }

        $bytes = str_split($rawTrackData);

        $position         = 0;
        $initialized      = false;
        $trackOneLength   = $trackOneLengthRounded   = 0;
        $trackTwoLength   = $trackTwoLengthRounded   = 0;
        $trackThreeLength = $trackThreeLengthRounded = 0;

        foreach ($bytes as $byte) {
            if (0 === $position && "\x02" !== $byte) {
                throw DecoderException::malformedTrackData();

            } elseif (5 === $position) {
                $trackOneLength        = ord($byte);
                $trackOneLengthRounded = ceil($trackOneLength / 8) * 8;

            } elseif (6 === $position) {
                $trackTwoLength        = ord($byte);
                $trackTwoLengthRounded = ceil($trackTwoLength / 8) * 8;

            } elseif (7 === $position) {
                $trackThreeLength        = ord($byte);
                $trackThreeLengthRounded = ceil($trackThreeLength / 8) * 8;

            } elseif (10 <= $position) {
                $initialized = true;

                break;
            }

            $position++;
        }

        if (!$initialized) {
            throw DecoderException::malformedTrackData();
        }

        $maskedTracks = substr($rawTrackData, 10, $trackOneLength + $trackTwoLength + $trackThreeLength);

        $position = 10 + $trackOneLength + $trackTwoLength + $trackThreeLength;
        $encryptedTrackOne = substr($rawTrackData, $position, $trackOneLengthRounded);
        $position += $trackOneLengthRounded;
        $encryptedTrackTwo = substr($rawTrackData, $position, $trackTwoLengthRounded);
        $position += $trackTwoLengthRounded;
        $encryptedTrackThree = substr($rawTrackData, $position, $trackThreeLengthRounded);

        $position += $trackThreeLengthRounded + 10;
        $ksn = substr($rawTrackData, $position, 10);

        $this->iso7813Decoder->decode($account, $maskedTracks);
        $account->setTrackOne($encryptedTrackOne);
        $account->setTrackTwo($encryptedTrackTwo);
        $account->setTrackThree($encryptedTrackThree);
        $account->setFormat(self::ENCRYPTION_FORMAT);
        $account->setKeySerialNumber($ksn);
    }
}
