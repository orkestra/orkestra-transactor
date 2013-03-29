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

use Orkestra\Transactor\Entity\Account\EncryptedSwipedCardAccount;
use Orkestra\Transactor\Entity\Account\SwipedCardAccount;
use Orkestra\Transactor\TrackDecoder\EnhancedEncryptionDecoder;
use Orkestra\Transactor\TrackDecoder\Iso7813Decoder;

/**
 * Unit tests for the Enhanced Encryption Decoder
 *
 * @group orkestra
 * @group transactor
 * @group decoder
 */
class EnhancedEncryptionDecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $trackData = pack('H*', '02D500801F3723008383252A353135302A2A2A2A2A2A2A2A373836315E504159504153532F4D4153544552434152445E2A2A2A2A2A2A2A2A2A2A2A2A2A2A2A3F2A3B353135302A2A2A2A2A2A2A2A373836313D2A2A2A2A2A2A2A2A2A2A2A2A2A2A2A3F2AA096A6F5D1DCBE45B5F77EB2559FEE0411013232E3F42044C0397E3E9E6D9B3A11FB8ADE0712AFD097C23AA86DFDC9DBA0E73A6FD698FD2F80800C0E1E9ED1BEED5EEA9840DA53F41254FDB79E89B76B127C25FE44AE7524BAEB5BDAACF777FA31323334353637383930FFFF9876543210E0004ABBF903');

        $decoder = new EnhancedEncryptionDecoder(new Iso7813Decoder());

        $this->assertTrue($decoder->supports($trackData));
        $this->assertFalse($decoder->supports('invalid data'));
    }

    public function testDecodeWithNonEncryptedAccountFails()
    {
        $decoder = new EnhancedEncryptionDecoder(new Iso7813Decoder());

        $account = new SwipedCardAccount();

        $this->setExpectedException('Orkestra\Transactor\Exception\DecoderException', 'Unable to populate account with data. An EncryptedSwipedCardAccount is necessary to decode encrypted track data.');

        $decoder->decode($account, '');
    }

    public function testDecode()
    {
        $trackData = pack('H*', '02D500801F3723008383252A353135302A2A2A2A2A2A2A2A373836315E504159504153532F4D4153544552434152445E2A2A2A2A2A2A2A2A2A2A2A2A2A2A2A3F2A3B353135302A2A2A2A2A2A2A2A373836313D2A2A2A2A2A2A2A2A2A2A2A2A2A2A2A3F2AA096A6F5D1DCBE45B5F77EB2559FEE0411013232E3F42044C0397E3E9E6D9B3A11FB8ADE0712AFD097C23AA86DFDC9DBA0E73A6FD698FD2F80800C0E1E9ED1BEED5EEA9840DA53F41254FDB79E89B76B127C25FE44AE7524BAEB5BDAACF777FA31323334353637383930FFFF9876543210E0004ABBF903');

        $decoder = new EnhancedEncryptionDecoder(new Iso7813Decoder());

        $account = new EncryptedSwipedCardAccount();
        $decoder->decode($account, $trackData);

        $this->assertEquals(
            'a096a6f5d1dcbe45b5f77eb2559fee0411013232e3f42044c0397e3e9e6d9b3a11fb8ade0712afd097c23aa86dfdc9dba0e73a6fd698fd2f',
            bin2hex($account->getTrackOne())
        );
        $this->assertEquals(
            '80800c0e1e9ed1beed5eea9840da53f41254fdb79e89b76b127c25fe44ae7524baeb5bdaacf777fa',
            bin2hex($account->getTrackTwo())
        );
        $this->assertEquals(
            '',
            $account->getTrackThree()
        );
        $this->assertEquals('Mastercard Paypass', $account->getName());
        $this->assertEquals('5150********7861', $account->getAccountNumber());
        $this->assertEquals('ffff9876543210e0004a', bin2hex($account->getKeySerialNumber()));
    }
}
