<?php

namespace Orkestra\Transactor\Model;

/**
 * Represents credentials used to authenticate with a transactor
 */
interface CredentialsInterface
{
    /**
     * Set Credential
     *
     * @param string $key The key of which credential to set
     * @param mixed  $value
     */
    public function setCredential($key, $value);

    /**
     * Get Credential
     *
     * @param  string $key The key of which credential to get
     *
     * @return mixed
     */
    public function getCredential($key);

    /**
     * Gets the credentials
     *
     * @return array
     */
    public function getCredentials();

    /**
     * Sets the credentials
     *
     * @param $value
     */
    public function setCredentials($value);

    /**
     * Sets the transactor
     *
     * @param \Orkestra\Transactor\TransactorInterface|string $transactor
     */
    public function setTransactor($transactor);

    /**
     * Gets the transactor
     *
     * @return string
     */
    public function getTransactor();
}