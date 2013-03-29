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
use Orkestra\Transactor\Type\Month;
use Orkestra\Transactor\Type\Year;

/**
 * Supports ISO 7813 Format B
 */
class Iso7813Decoder implements DecoderInterface
{
    /**
     * @param $rawTrackData
     *
     * @return bool
     */
    public function supports($rawTrackData)
    {
        return '%B' === substr($rawTrackData, 0, 2);
    }

    /**
     * Decodes the given raw track data
     *
     * @param \Orkestra\Transactor\Entity\Account\SwipedCardAccount $account
     * @param string                                                $rawTrackData
     *
     * @return \Orkestra\Transactor\Entity\Account\SwipedCardAccount
     */
    public function decode(SwipedCardAccount $account, $rawTrackData)
    {
        $tracks = explode('?', $rawTrackData);

        $account->setTrackOne(isset($tracks[0])   ? $tracks[0] . '?' : null);
        $account->setTrackTwo(isset($tracks[1])   ? $tracks[1] . '?' : null);
        $account->setTrackThree(isset($tracks[2]) ? $tracks[2] . '?' : null);

        if (!empty($tracks[0])) {
            $parts = explode('^', $tracks[0]);
            $names = explode('/', $parts[1]);

            $account->setAccountNumber(substr($parts[0], 2));
            $account->setName(sprintf(
                '%s %s',
                ucfirst(strtolower(trim($names[1]))),
                ucfirst(strtolower(trim($names[0])))
            ));
            $this->trySetMonth($account, substr($parts[2], 2, 2));
            $this->trySetYear($account, '20' . substr($parts[2], 0, 2));

        } elseif (!empty($tracks[1])) {
            $parts = explode('=', $tracks[1]);

            $account->setAccountNumber(substr($parts[0], 1));
            $this->trySetMonth($account, substr($parts[1], 2, 2));
            $this->trySetYear($account, '20' . substr($parts[1], 0, 2));

        } elseif (!empty($tracks[2])) {
            $parts = explode('=', $tracks[2]);

            $account->setAccountNumber(substr($parts[0], 3));
            $this->trySetMonth($account, substr($parts[1], -3, 2));
            $this->trySetYear($account, '20' . substr($parts[1], -5, 2));
        }

        return $account;
    }

    private function trySetMonth(SwipedCardAccount $account, $value)
    {
        try {
            $account->setExpMonth(new Month($value));
        } catch (\Exception $e) { }
    }

    private function trySetYear(SwipedCardAccount $account, $value)
    {
        try {
            $account->setExpYear(new Year($value));
        } catch (\Exception $e) { }
    }
}
