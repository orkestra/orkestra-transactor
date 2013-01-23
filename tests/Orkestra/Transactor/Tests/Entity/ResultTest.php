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

use Orkestra\Transactor\Entity\Result;
use Orkestra\Transactor\Entity\Transaction;

/**
 * Unit tests for the Result entity
 *
 * @group orkestra
 * @group transactor
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testSettingStatusSetsTransactionAndParentStatus()
    {
        $parent = new Transaction();
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $result = $transaction->getResult();

        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $result->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $transaction->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $parent->getStatus()->getValue());

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::APPROVED, $transaction->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::APPROVED, $parent->getStatus()->getValue());
    }

    public function testSettingErrorOrUnprocessedStatusDoesNotChangeParentStatus()
    {
        $parent = new Transaction();
        $transaction = $parent->createChild(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $result = $transaction->getResult();

        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $result->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $transaction->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $parent->getStatus()->getValue());

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::PENDING));

        $this->assertEquals(Result\ResultStatus::PENDING, $parent->getStatus()->getValue());

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::UNPROCESSED));

        $this->assertEquals(Result\ResultStatus::UNPROCESSED, $transaction->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::PENDING, $parent->getStatus()->getValue());

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::ERROR));

        $this->assertEquals(Result\ResultStatus::ERROR, $transaction->getStatus()->getValue());
        $this->assertEquals(Result\ResultStatus::PENDING, $parent->getStatus()->getValue());
    }

    public function testSettingStatusSetsTransacted()
    {
        $transaction = new Transaction();
        $result = $transaction->getResult();

        $this->assertFalse($result->isTransacted());

        $result->setStatus(new Result\ResultStatus(Result\ResultStatus::APPROVED));

        $this->assertEquals(Result\ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertTrue($result->isTransacted());
    }
}
