<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\TrackDecoder;

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\TrackDecoder\Iso7813Decoder;

/**
 * Unit tests for the ISO 7813 Decoder
 *
 * @group orkestra
 * @group transactor
 * @group decoder
 */
class Iso7813DecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $trackData = '%B1234567890123445^SOMMER/T.                 ^12011200000000000000**XXX******?;1234567890123445=12011200XXXX00000000??';

        $decoder = new Iso7813Decoder();

        $this->assertTrue($decoder->supports($trackData));
        $this->assertFalse($decoder->supports('invalid data'));
    }

    public function testDecodeTrackOne()
    {
        $trackData = '%B1234567890123445^SOMMER/T.                 ^12011200000000000000**XXX******???';

        $decoder = new Iso7813Decoder();

        $account = new SwipedCardAccount();
        $decoder->decode($account, $trackData);

        $this->assertEquals('%B1234567890123445^SOMMER/T.                 ^12011200000000000000**XXX******?', $account->getTrackOne());
        $this->assertEquals('?', $account->getTrackTwo());
        $this->assertEquals('?', $account->getTrackThree());
        $this->assertEquals('T. Sommer', $account->getName());
        $this->assertEquals('1234567890123445', $account->getAccountNumber());
        $this->assertEquals('January', $account->getExpMonth()->getLongName());
        $this->assertEquals('2012', $account->getExpYear()->getLongYear());
    }

    public function testDecodeTrackTwo()
    {
        $trackData = '?;1234567890123445=12011200XXXX00000000??';

        $decoder = new Iso7813Decoder();

        $account = new SwipedCardAccount();
        $decoder->decode($account, $trackData);

        $this->assertEquals('?', $account->getTrackOne());
        $this->assertEquals(';1234567890123445=12011200XXXX00000000?', $account->getTrackTwo());
        $this->assertEquals('?', $account->getTrackThree());
        $this->assertEquals('1234567890123445', $account->getAccountNumber());
        $this->assertEquals('January', $account->getExpMonth()->getLongName());
        $this->assertEquals('2012', $account->getExpYear()->getLongYear());
    }

    public function testDecodeTrackThree()
    {
        $trackData = '??;011234567890123445=724724100000000000030300XXXX040400012010=************************==1=0000000000000000?';

        $decoder = new Iso7813Decoder();

        $account = new SwipedCardAccount();
        $decoder->decode($account, $trackData);

        $this->assertEquals('?', $account->getTrackOne());
        $this->assertEquals('?', $account->getTrackTwo());
        $this->assertEquals(';011234567890123445=724724100000000000030300XXXX040400012010=************************==1=0000000000000000?', $account->getTrackThree());
        $this->assertEquals('1234567890123445', $account->getAccountNumber());
        $this->assertEquals('January', $account->getExpMonth()->getLongName());
        $this->assertEquals('2012', $account->getExpYear()->getLongYear());
    }
}
