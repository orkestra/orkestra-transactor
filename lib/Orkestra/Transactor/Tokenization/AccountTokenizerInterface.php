<?php

namespace Orkestra\Transactor\Token;

use Orkestra\Transactor\Entity\AbstractAccount;
use Orkestra\Transactor\Entity\Credentials;

/**
 * Defines the contract any account tokenizer must follow.
 * 
 * A TokenAccount factory uses some external resource to transform other
 * accounts into a tokenized version.
 */
interface AccountTokenizerInterface
{
    /**
     * Tokenizes an account using the given Credentials
     * 
     * @param AbstractAccount $account
     * @param Credentials     $credentials
     *
     * @return AbstractAccount
     */
    public function tokenizeAccount(AbstractAccount $account, Credentials $credentials);
}