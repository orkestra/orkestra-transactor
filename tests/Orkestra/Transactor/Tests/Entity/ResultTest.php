<?php

namespace Orkestra\Transactor\Tests\Entity;

require_once __DIR__ . '/../../../../bootstrap.php';

use Orkestra\Transactor\Entity\Result;

/**
 * Unit tests for the Result entity
 *
 * @group orkestra
 * @group transactor
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $result = new Result();

        $this->assertEquals(Result\ResultType::UNPROCESSED, $result->getType()->getValue());
    }

    public function testIsTransacted()
    {
        $result = new Result();

        $this->assertFalse($result->isTransacted());

        $result->setType(new Result\ResultType(Result\ResultType::APPROVED));

        $this->assertEquals(Result\ResultType::APPROVED, $result->getType()->getValue());
        $this->assertTrue($result->isTransacted());
    }
}
