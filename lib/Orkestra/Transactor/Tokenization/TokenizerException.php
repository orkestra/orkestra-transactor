<?php

namespace Orkestra\Transactor\Tokenization;

/**
 * An error occurred during tokenization
 */
class TokenizerException extends \RuntimeException
{
    /**
     * Thrown when a tokenizer fails to tokenize an account
     * 
     * @param string $reason
     *
     * @return TokenizerException
     */
    public static function tokenizationFailed($reason = 'An error occurred')
    {
        return new static(sprintf('Unable to tokenize account: %s', $reason));
    }

    /**
     * Thrown when an Account already has Credentials which are not the same as those passed
     * 
     * @return TokenizerException
     */
    public static function credentialsExistAndDoNotMatch()
    {
        return new static('The given account already has credentials which do not match the given credentials.');
    }

    /**
     * Throw when an Account is already tokenized
     * 
     * @return TokenizerException
     */
    public static function alreadyTokenized()
    {
        return new static('The account is already tokenized');
    }
}