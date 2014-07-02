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
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\Result\ResultStatus;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
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
        $this->assertTrue($transactor->supportsNetwork(new NetworkType(NetworkType::POINTS)));

        // Unsupported
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CHECK)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::ACH)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::MFA)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CARD)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::CASH)));
        $this->assertFalse($transactor->supportsNetwork(new NetworkType(NetworkType::SWIPED)));
    }

    public function testSupportsCorrectTypes()
    {
        $transactor = new PointsTransactor();

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

    public function testAccountMustHaveEnoughPoints()
    {
        $account = new PointsAccount();
        $account->setBalance(10);

        $transaction = new Transaction();
        $transaction->setAccount($account);
        $transaction->setNetwork(new NetworkType(NetworkType::POINTS));
        $transaction->setType(new TransactionType(TransactionType::SALE));
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
        $transaction->setNetwork(new NetworkType(NetworkType::POINTS));
        $transaction->setType(new TransactionType(TransactionType::SALE));
        $transaction->setAmount(95);

        $transactor = new PointsTransactor();

        $result = $transactor->transact($transaction);

        $this->assertEquals('orkestra.generic.points', $result->getTransactor());
        $this->assertEquals(ResultStatus::APPROVED, $result->getStatus());
        $this->assertEquals(5, $account->getBalance());
    }
}
