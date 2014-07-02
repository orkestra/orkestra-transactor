<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Entity;

use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateChild()
    {
        $parent = new Transaction();
        $parent->setAmount(10.00);
        $parent->setNetwork(new NetworkType(NetworkType::CARD));
        $parent->setType(new TransactionType(TransactionType::AUTH));

        $parentResult = $parent->getResult();
        $parentResult->setStatus(new ResultStatus(ResultStatus::CANCELLED));

        $child = $parent->createChild(new TransactionType(TransactionType::CAPTURE), 12.50);

        $this->assertEquals(12.50, $child->getAmount());
        $this->assertEquals(NetworkType::CARD, $child->getNetwork());
        $this->assertEquals(TransactionType::CAPTURE, $child->getType());
        $this->assertEquals(ResultStatus::UNPROCESSED, $child->getResult()->getStatus());
        $this->assertSame($parent, $child->getParent());
        $this->assertTrue($parent->getChildren()->contains($child));
    }
}
