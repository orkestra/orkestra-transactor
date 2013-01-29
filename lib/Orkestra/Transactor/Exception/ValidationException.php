<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Exception;

use Orkestra\Transactor\Entity\AbstractAccount;

/**
 * An exception that occurs when a transaction is being validated
 */
class ValidationException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = 'Validation failed: ' . $message;
    }

    /**
     * Occurs when the Transactor requires that a transaction have a parent transaction
     *
     * @static
     * @return ValidationException
     */
    public static function parentTransactionRequired()
    {
        return new self('parent transaction is required to transact.');
    }

    /**
     * Occurs when the Transactor requires account information not available on the transaction
     *
     * @static
     * @return ValidationException
     */
    public static function missingAccountInformation()
    {
        return new self('account information is missing or invalid.');
    }

    /**
     * Occurs when the Transactor requires credentials not available on the transaction
     *
     * @static
     * @return ValidationException
     */
    public static function missingCredentials()
    {
        return new self('credentials are missing or invalid.');
    }

    /**
     * Occurs when the Transactor requires a parameter not available on the transaction
     *
     * @static
     * @param $parameter
     * @return ValidationException
     */
    public static function missingRequiredParameter($parameter)
    {
        return new self(sprintf('missing required parameter: %s', $parameter));
    }

    /**
     * Occurs where an invalid account type is used
     *
     * @static
     * @param  \Orkestra\Transactor\Entity\AbstractAccount $account
     * @return ValidationException
     */
    public static function invalidAccountType(AbstractAccount $account = null)
    {
        return new self(sprintf('invalid account type: %s', $account ? $account->getType() : 'null'));
    }
}
