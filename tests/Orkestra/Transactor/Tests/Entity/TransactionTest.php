<?php

namespace Orkestra\Transactor\Tests\Entity;

use Orkestra\Transactor\Entity\Result\ResultStatus;
use Orkestra\Transactor\Entity\Transaction;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateChild()
    {
        $parent = new Transaction();
        $parent->setAmount(10.00);
        $parent->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD));
        $parent->setType(new Transaction\TransactionType(Transaction\TransactionType::AUTH));

        $parentResult = $parent->getResult();
        $parentResult->setStatus(new ResultStatus(ResultStatus::CANCELLED));

        $child = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::CAPTURE), 12.50);

        $this->assertEquals(12.50, $child->getAmount());
        $this->assertEquals(Transaction\NetworkType::CARD, $child->getNetwork());
        $this->assertEquals(Transaction\TransactionType::CAPTURE, $child->getType());
        $this->assertEquals(ResultStatus::UNPROCESSED, $child->getResult()->getStatus());
        $this->assertSame($parent, $child->getParent());
        $this->assertTrue($parent->getChildren()->contains($child));
    }
}
