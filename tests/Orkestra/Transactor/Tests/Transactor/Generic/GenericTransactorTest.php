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

use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Transactor\Generic\GenericTransactor;

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
        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::CASH)));
        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::CHECK)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::POINTS)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::SWIPED)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new GenericTransactor();

        // Supported
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::SALE)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::CREDIT)));
        $this->assertTrue($transactor->supportsType(new TransactionType(TransactionType::REFUND)));

        // Unsupported
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::AUTH)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::CAPTURE)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::VOID)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::QUERY)));
        $this->assertFalse($transactor->supportsType(new TransactionType(TransactionType::UPDATE)));
    }

    public function testApproval()
    {
        $transaction = new Transaction();
        $transaction->setNetwork(new NetworkType(NetworkType::CASH));
        $transaction->setType(new TransactionType(TransactionType::SALE));

        $transactor = new GenericTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals('orkestra.generic', $result->getTransactor());
        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus());
    }
}
