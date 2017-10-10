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

use Orkestra\Transactor\Entity\Account\PointsAccount;
use Orkestra\Transactor\Entity\Result\ResultStatus;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Transactor\Generic\PointsTransactor;

/**
 * Unit tests for the Points Transactor
 *
 * @group orkestra
 * @group transactor
 */
class PointsTransactorTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsCorrectNetworks()
    {
        $transactor = new PointsTransactor();

        // Supported
        $this->assertTrue($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::POINTS)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CHECK)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::CASH)));
        $this->assertFalse($transactor->supportsNetwork(new Transaction\NetworkType(Transaction\NetworkType::SWIPED)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new PointsTransactor();

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

    public function testAccountMustHaveEnoughPoints()
    {
        $account = new PointsAccount();
        $account->setBalance(10);

        $transaction = new Transaction();
        $transaction->setAccount($account);
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::POINTS));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setAmount(100);

        $transactor = new PointsTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals('orkestra.generic.points', $result->getTransactor());
        $this->assertEquals(ResultStatus::DECLINED, $result->getStatus());
        $this->assertEquals('Amount exceeds account balance', $result->getMessage());
    }

    public function testApproval()
    {
        $account = new PointsAccount();
        $account->setBalance(100);

        $transaction = new Transaction();
        $transaction->setAccount($account);
        $transaction->setNetwork(new Transaction\NetworkType(Transaction\NetworkType::POINTS));
        $transaction->setType(new Transaction\TransactionType(Transaction\TransactionType::SALE));
        $transaction->setAmount(95);

        $transactor = new PointsTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals('orkestra.generic.points', $result->getTransactor());
        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus());
        $this->assertEquals(5, $account->getBalance());
    }
}
