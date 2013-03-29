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

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;

/**
 * Defines the contract any Decoder must follow
 *
 * A decoder is responsible for reading and decoding magnetic stripe data and creating
 * a resulting SwipedCardAccount from the read data.
 */
interface DecoderInterface
{
    /**
     * @param $rawTrackData
     *
     * @return bool
     */
    public function supports($rawTrackData);

    /**
     * Decodes the raw track data and populates the given account
     *
     * @param \Orkestra\Transactor\Entity\Account\SwipedCardAccount $account
     * @param string                                                $rawTrackData
     *
     * @return void
     *
     * @throws \Orkestra\Transactor\Exception\DecoderException If unable to decode
     */
    public function decode(SwipedCardAccount $account, $rawTrackData);
}
