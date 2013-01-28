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

use Orkestra\Transactor\Entity\Result\ResultStatus;
use Orkestra\Transactor\Entity\Transaction;
use Orkestra\Transactor\Exception\TransactorException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Base class for any Transactor
 */
abstract class AbstractTransactor implements TransactorInterface
{
    /**
     * @var array $_supportedNetworks An array of NetworkType constants
     */
    protected static $supportedNetworks = array();

    /**
     * @var array $_supportedTypes An array of TransactionType constants
     */
    protected static $supportedTypes = array();

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolverInterface
     */
    private $resolver;

    /**
     * Transacts the given transaction
     *
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array                                   $options
     *
     * @throws \Orkestra\Transactor\Exception\TransactorException
     * @return \Orkestra\Transactor\Entity\Result
     */
    public function transact(Transaction $transaction, array $options = array())
    {
        if ($transaction->isTransacted()) {
            throw TransactorException::transactionAlreadyProcessed();
        } elseif (!$this->supportsType($transaction->getType())) {
            throw TransactorException::unsupportedTransactionType($transaction->getType());
        } elseif (!$this->supportsNetwork($transaction->getNetwork())) {
            throw TransactorException::unsupportedTransactionNetwork($transaction->getNetwork());
        }

        $result = $transaction->getResult();
        $result->setTransactor($this);

        try {
            $options = $this->getResolver()->resolve($options);

            $this->doTransact($transaction, $options);
        } catch (\Exception $e) {
            $result->setStatus(new ResultStatus(ResultStatus::ERROR));
            $result->setMessage('An internal error occurred while processing the transaction.');
            $result->setData('message', $e->getMessage());
            $result->setData('trace', $e->getTraceAsString());
        }

        return $result;
    }

    /**
     * Transacts the given transaction
     *
     * @param \Orkestra\Transactor\Entity\Transaction $transaction
     * @param array                                   $options
     *
     * @return \Orkestra\Transactor\Entity\Result
     */
    abstract protected function doTransact(Transaction $transaction, array $options = array());

    /**
     * Configures the transactors OptionsResolver
     *
     * Override this method to tell the resolver what options are available.
     * This resolver will be used to validate the options passed to the
     * transact method.
     *
     * @see transact
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     *
     * @return void
     */
    protected function configureResolver(OptionsResolverInterface $resolver)
    {
    }

    /**
     * @return \Symfony\Component\OptionsResolver\OptionsResolverInterface
     */
    private function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new OptionsResolver();
            $this->configureResolver($this->resolver);
        }

        return $this->resolver;
    }


    /**
     * Returns true if this Transactor supports a given Transaction type
     *
     * @param  \Orkestra\Transactor\Entity\Transaction\TransactionType|null $type
     *
     * @return boolean True if supported
     */
    public function supportsType(Transaction\TransactionType $type = null)
    {
        return in_array((null === $type ? null : $type->getValue()), static::$supportedTypes);
    }

    /**
     * Returns true if this Transactor supports a given Network type
     *
     * @param \Orkestra\Transactor\Entity\Transaction\NetworkType|null $network
     *
     * @return boolean True if supported
     */
    public function supportsNetwork(Transaction\NetworkType $network = null)
    {
        return in_array((null === $network ? null : $network->getValue()), static::$supportedNetworks);
    }

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->getName(), $this->getType());
    }
}
