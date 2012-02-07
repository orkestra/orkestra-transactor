<?php

namespace Orkestra\Transactor\Exception;

/**
 * Validation Exception
 *
 * @package Orkestra
 * @subpackage Transactor
 */
class ValidationException extends \Exception
{
    public function __construct($message)
    {
        $this->message = 'Validation failed: ' . $message;
    }
    
    public static function parentTransactionRequired()
    {
        return new self('parent transaction is required to transact.');
    }
    
    public static function missingAccountInformation()
    {
        return new self('account information is missing or invalid.');
    }
    
    public static function missingRequiredParameter($parameter)
    {
        return new self(sprintf('missing required parameter: %s', $parameter));
    }
}