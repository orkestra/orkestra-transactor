<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Transactor\Generic;

use Orkestra\Transactor\Entity\Result\ResultStatus;
use Orkestra\Transactor\Transactor\Generic\GenericTransactor;
use Orkestra\Transactor\Entity\Transaction;

/**
 * Unit tests for the Generic Transactor
 *
 * @group orkestra
 * @group transactor
 */
class GenericTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = new GenericTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CASH)));
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CHECK)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::POINTS)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::SWIPED)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new GenericTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::SALE)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CREDIT)));
        $this->assertTrue($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::REFUND)));

        // Unsupported
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::AUTH)));
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::CAPTURE)));
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::VOID)));
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::QUERY)));
        $this->assertFalse($transactor->supportsType(new Transaction\TransactionType(Transaction\TransactionType::UPDATE)));
    }

    public function testApproval()
    {
        $transaction = new Transaction();
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::CASH));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));

        $transactor = new GenericTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals('orkestra.generic', $result->getTransactor());
        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus());
    }
}
