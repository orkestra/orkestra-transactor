<?php

namespace Orkestra\Transactor\Tests\Type;

use Orkestra\Transactor\Type\Year;

/**
 * Tests the functionality provided by the Year data type
 *
 * @group orkestra
 * @group transactor
 */
class YearTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidYear()
    {
        $this->setExpectedException('InvalidArgumentException', '12 is not a valid value');

        $year = new Year(12);
    }

    public function testGetters()
    {
        $year = new Year(2012);

        $this->assertEquals('12', $year->getShortYear());
        $this->assertEquals('2012', $year->getLongYear());

        $this->assertEquals($year->getLongYear(), $year->__toString());
    }
}
