<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor;

use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Model\CredentialsInterface;
use Orkestra\Transactor\Model\ResultInterface;
use Orkestra\Transactor\Model\Transaction\NetworkType;
use Orkestra\Transactor\Model\Transaction\TransactionType;
use Orkestra\Transactor\Model\TransactionInterface;

/**
 * Defines the contract any Transactor must follow
 */
interface TransactorInterface
{
    /**
     * Transacts a transaction and returns the result
     *
     * @abstract
     *
     * @param TransactionInterface $transaction
     * @param array                $options
     *
     * @return ResultInterface
     */
    public function transact(TransactionInterface $transaction, array $options = array());

    /**
     * Returns true if the Transactor supports the given transaction type
     *
     * @abstract
     * @param  TransactionType $type
     * @return boolean
     */
    public function supportsType(TransactionType $type = null);

    /**
     * Returns true if the Transactor supports the given network type
     *
     * @abstract
     * @param  NetworkType $network
     * @return boolean
     */
    public function supportsNetwork(NetworkType $network = null);

    /**
     * Returns the internally used type of this Transactor
     *
     * @abstract
     * @return string
     */
    public function getType();

    /**
     * Returns the name of this Transactor
     *
     * @abstract
     * @return string
     */
    public function getName();

    /**
     * Creates a new, empty Credentials entity
     *
     * This method should set the appropriate fields to their initial value
     *
     * @return CredentialsInterface
     */
    public function createCredentials();
}
