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

/**
 * Defines the contract any TransactorFactory must follow
 *
 * A TransactorFactory provides a single place, registry-style, for transactors
 * to be registered with and retrieved from.
 */
interface TransactorFactoryInterface
{
    /**
     * Gets all available Transactors
     *
     * @return array
     */
    public function getTransactors();

    /**
     * Gets a single transactor by name
     *
     * @param string $name
     *
     * @return \Orkestra\Transactor\TransactorInterface
     *
     * @throws \Orkestra\Transactor\Exception\TransactorException if there is no Transactor by the given name
     */
    public function getTransactor($name);
}
