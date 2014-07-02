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

use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\Result\ResultStatus;

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
        $transaction = $parent->createChild(new TransactionType(TransactionType::SALE));
        $result = $transaction->getResult();

        $this->assertEquals(ResultStatus::UNPROCESSED, $result->getStatus()->getValue());
        $this->assertEquals(ResultStatus::UNPROCESSED, $transaction->getStatus()->getValue());
        $this->assertEquals(ResultStatus::UNPROCESSED, $parent->getStatus()->getValue());

        $result->setStatus(new ResultStatus(ResultStatus::APPROVED));

        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertEquals(ResultStatus::APPROVED, $transaction->getStatus()->getValue());
        $this->assertEquals(ResultStatus::APPROVED, $parent->getStatus()->getValue());
    }

    public function testSettingErrorOrUnprocessedStatusDoesNotChangeParentStatus()
    {
        $parent = new Transaction();
        $transaction = $parent->createChild(new TransactionType(TransactionType::SALE));
        $result = $transaction->getResult();

        $this->assertEquals(ResultStatus::UNPROCESSED, $result->getStatus()->getValue());
        $this->assertEquals(ResultStatus::UNPROCESSED, $transaction->getStatus()->getValue());
        $this->assertEquals(ResultStatus::UNPROCESSED, $parent->getStatus()->getValue());

        $result->setStatus(new ResultStatus(ResultStatus::PENDING));

        $this->assertEquals(ResultStatus::PENDING, $parent->getStatus()->getValue());

        $result->setStatus(new ResultStatus(ResultStatus::UNPROCESSED));

        $this->assertEquals(ResultStatus::UNPROCESSED, $transaction->getStatus()->getValue());
        $this->assertEquals(ResultStatus::PENDING, $parent->getStatus()->getValue());

        $result->setStatus(new ResultStatus(ResultStatus::ERROR));

        $this->assertEquals(ResultStatus::ERROR, $transaction->getStatus()->getValue());
        $this->assertEquals(ResultStatus::PENDING, $parent->getStatus()->getValue());
    }

    public function testSettingStatusSetsTransacted()
    {
        $transaction = new Transaction();
        $result = $transaction->getResult();

        $this->assertFalse($result->isTransacted());

        $result->setStatus(new ResultStatus(ResultStatus::APPROVED));

        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus()->getValue());
        $this->assertTrue($result->isTransacted());
    }
}
