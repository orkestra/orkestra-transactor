<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

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