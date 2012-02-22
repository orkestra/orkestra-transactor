<?php

namespace Orkestra\Transactor\Tests\Type;

use Orkestra\Transactor\Type\Month;

/**
 * Month Test
 *
 * Tests the functionality provided by the Month data type
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