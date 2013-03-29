<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Exception;

/**
 * An exception that occurs when dealing with Decoders
 */
class DecoderException extends \Exception
{
    /**
     * Thrown when a Decoder encounters malformed track data
     *
     * @return DecoderException
     */
    public static function malformedTrackData()
    {
        return new self('Unable to decode track data. Data is malformed.');
    }
}
