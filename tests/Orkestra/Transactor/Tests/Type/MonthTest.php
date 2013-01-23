<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Type;

use Orkestra\Transactor\Type\Month;

/**
 * Tests the functionality provided by the Month data type
 *
 * @group orkestra
 * @group transactor
 */
class MonthTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidMonth()
    {
        $this->setExpectedException('InvalidArgumentException', '13 is not a valid value');

        $month = new Month(13);
    }

    public function testGetters()
    {
        $month = new Month(7);

        $this->assertEquals('Jul', $month->getShortName());
        $this->assertEquals('July', $month->getLongName());
        $this->assertEquals('7', $month->getShortMonth());
        $this->assertEquals('07', $month->getLongMonth());

        $this->assertEquals($month->getLongName(), $month->__toString());
    }
}
