<?php

namespace Orkestra\Transactor\Tokenization;

use Orkestra\Transactor\Entity\Credentials;
use Orkestra\Transactor\Model\AccountInterface;

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
     * @param AccountInterface $account
     * @param Credentials      $credentials
     *
     * @return AccountInterface
     */
    public function tokenizeAccount(AccountInterface $account, Credentials $credentials);
}
