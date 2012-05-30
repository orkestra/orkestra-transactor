<?php

namespace Orkestra\Transactor;

use Orkestra\Transactor\Exception\TransactorException;

/**
 * Responsible for managing available Transactors
 */
class TransactorFactory
{
    /**
     * @var array
     */
    protected $_transactors = array();

    /**
     * Registers a Transactor with the factory
     *
     * @param \Orkestra\Transactor\TransactorInterface $transactor
     */
    public function registerTransactor(TransactorInterface $transactor)
    {
        $this->_transactors[$transactor->getName()] = $transactor;
    }

    /**
     * Gets all available Transactors
     *
     * @return array
     */
    public function getTransactors()
    {
        return array_values($this->_transactors);
    }

    /**
     * Gets a single transactor by name
     *
     * @param string $name
     * @return \Orkestra\Transactor\TransactorInterface
     * @throws \Orkestra\Transactor\Exception\TransactorException if there is no Transactor by the given name
     */
    public function getTransactor($name)
    {
        if (!array_key_exists($name, $this->_transactors)) {
            throw TransactorException::transactorNotRegistered($name);
        }

        return $this->_transactors[$name];
    }
}
