<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Model;

/**
 * An account used to process a transaction
 */
interface AccountInterface
{
    /**
     * Return a printable type name
     *
     * @return string
     */
    public function getType();

    /**
     * Set Account Number
     *
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber);

    /**
     * Get Account Number
     *
     * @return string
     */
    public function getAccountNumber();

    /**
     * Set IP Address
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress);

    /**
     * Get IP Address
     *
     * @return string
     */
    public function getIpAddress();

    /**
     * Add Transaction
     *
     * @param TransactionInterface $transaction
     */
    public function addTransaction(TransactionInterface $transaction);

    /**
     * Get Transactions
     *
     * @return TransactionInterface[]
     */
    public function getTransactions();

    /**
     * Set Phone Number
     *
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber);

    /**
     * Get Phone Number
     *
     * @return string
     */
    public function getPhoneNumber();

    /**
     * Sets the name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Gets the name
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the address
     *
     * @param string $address
     */
    public function setAddress($address);

    /**
     * Gets the address
     *
     * @return string
     */
    public function getAddress();

    /**
     * Sets the city
     *
     * @param string $city
     */
    public function setCity($city);

    /**
     * Gets the city
     *
     * @return string
     */
    public function getCity();

    /**
     * Sets the postal code
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode);

    /**
     * Gets the postal code
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Sets the region
     *
     * @param string $region
     */
    public function setRegion($region);

    /**
     * Gets the region
     *
     * @return string
     */
    public function getRegion();

    /**
     * Sets the country
     *
     * @param string $country
     */
    public function setCountry($country);

    /**
     * Gets the country
     *
     * @return string
     */
    public function getCountry();

    /**
     * Sets the alias for this account
     *
     * @param string $alias
     */
    public function setAlias($alias);

    /**
     * Gets the alias for this account
     *
     * @return string
     */
    public function getAlias();

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress);

    /**
     * @return string
     */
    public function getEmailAddress();

    /**
     * @param CredentialsInterface $credentials
     */
    public function setCredentials(CredentialsInterface $credentials = null);

    /**
     * @return CredentialsInterface
     */
    public function getCredentials();

    /**
     * @return string
     */
    public function getLastFour();
}