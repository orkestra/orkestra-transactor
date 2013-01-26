<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Entity\Account;

use Orkestra\Transactor\Entity\Account\SwipedCardAccount;

/**
 * Unit tests for the SwipedCardAccount entity
 *
 * @group orkestra
 * @group transactor
 */
class SwipedCardAccountTest extends \PHPUnit_Framework_TestCase
{
    public function testTrackOneParsing()
    {
        $account = new SwipedCardAccount();
        $account->setTrackOne('%B1234567890123445^SOMMER/T.                 ^12011200000000000000**XXX******?*');
        $account->prePersist();

        $this->assertEquals('1234567890123445', $account->getAccountNumber());
        $this->assertEquals('January', $account->getExpMonth()->getLongName());
        $this->assertEquals('2012', $account->getExpYear()->getLongYear());
    }

    public function testTrackTwoParsing()
    {
        $account = new SwipedCardAccount();
        $account->setTrackTwo(';1234567890123445=12011200XXXX00000000?*');
        $account->prePersist();

        $this->assertEquals('1234567890123445', $account->getAccountNumber());
        $this->assertEquals('January', $account->getExpMonth()->getLongName());
        $this->assertEquals('2012', $account->getExpYear()->getLongYear());
    }

    public function testTrackThreeParsing()
    {
        $account = new SwipedCardAccount();
        $account->setTrackThree(';011234567890123445=724724100000000000030300XXXX040400012010=************************==1=0000000000000000?*');
        $account->preUpdate();

        $this->assertEquals('1234567890123445', $account->getAccountNumber());
        $this->assertEquals('January', $account->getExpMonth()->getLongName());
        $this->assertEquals('2012', $account->getExpYear()->getLongYear());
    }
}
