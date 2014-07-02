<?php

namespace Orkestra\Transactor\Model\Account;

/**
 * An account that exists externally
 */
interface ExternalAccountInterface
{
    /**
     * @param string $externalAccountId
     */
    public function setExternalAccountId($externalAccountId);

    /**
     * @return string
     */
    public function getExternalAccountId();

    /**
     * @param string $externalPersonId
     */
    public function setExternalPersonId($externalPersonId);

    /**
     * @return string
     */
    public function getExternalPersonId();
}