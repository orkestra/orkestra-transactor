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

use Orkestra\Transactor\Exception\TransactorException;

/**
 * Responsible for managing available Transactors
 */
class TransactorFactory implements TransactorFactoryInterface
{
    /**
     * @var array
     */
    private $transactors = array();

    /**
     * Registers a Transactor with the factory
     *
     * @param \Orkestra\Transactor\TransactorInterface $transactor
     */
    public function registerTransactor(TransactorInterface $transactor)
    {
        $this->transactors[$transactor->getType()] = $transactor;
    }

    /**
     * Gets all available Transactors
     *
     * @return array
     */
    public function getTransactors()
    {
        return array_values($this->transactors);
    }

    /**
     * Gets a single transactor by name
     *
     * @param  string  $name
     *
     * @return \Orkestra\Transactor\TransactorInterface
     *
     * @throws \Orkestra\Transactor\Exception\TransactorException if there is no Transactor by the given name
     */
    public function getTransactor($name)
    {
        if (!array_key_exists($name, $this->transactors)) {
            throw TransactorException::transactorNotRegistered($name);
        }

        return $this->transactors[$name];
    }
}
