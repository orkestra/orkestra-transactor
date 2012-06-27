<?php

namespace Orkestra\Transactor\Tests\Entity;

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

        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $result->getStatus()->getValue());
    }

    public function testIsTransacted()
    {
        $result = new Result();

        $this->assertFalse($result->isTransacted());

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertTrue($result->isTransacted());
    }
}
