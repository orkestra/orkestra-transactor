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
use Orkestra\Transactor\Exception\DecoderException;

/**
 * A chain of decoders
 */
class ChainDecoder implements DecoderInterface
{
    /**
     * @var array|DecoderInterface[]
     */
    private $decoders = array();

    /**
     * Register a decoder in this chain
     *
     * @param DecoderInterface $decoder
     */
    public function registerDecoder(DecoderInterface $decoder)
    {
        if (!in_array($decoder, $this->decoders)) {
            $this->decoders[] = $decoder;
        }
    }

    /**
     * Gets the decoders registered in this chain
     *
     * @return array|DecoderInterface[]
     */
    public function getDecoders()
    {
        return $this->decoders;
    }

    /**
     * @param \Orkestra\Transactor\Entity\Account\SwipedCardAccount $account
     * @param string $rawTrackData Binary track data
     *
     * @return void
     *
     * @throws \Orkestra\Transactor\Exception\DecoderException
     */
    public function decode(SwipedCardAccount $account, $rawTrackData)
    {
        $decoder = $this->getDecoderFor($rawTrackData);

        if (!$decoder) {
            throw new DecoderException('Unable to locate suitable decoder for the given track data');
        }

        $decoder->decode($account, $rawTrackData);
    }

    /**
     * @param string $rawTrackData
     *
     * @return null|DecoderInterface
     */
    private function getDecoderFor($rawTrackData)
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supports($rawTrackData)) {
                return $decoder;
            }
        }

        return null;
    }

    /**
     * @param $rawTrackData
     *
     * @return bool
     */
    public function supports($rawTrackData)
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supports($rawTrackData)) {
                return true;
            }
        }

        return false;
    }
}
