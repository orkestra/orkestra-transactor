<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Entity;

use Orkestra\Transactor\Entity\Credentials;

/**
 * Unit tests for the Credentials entity
 *
 * @group orkestra
 * @group transactor
 */
class CredentialsTest extends \PHPUnit_Framework_TestCase
{
    public function testMagic()
    {
        $credentials = new Credentials();
        $credentials->username = 'test';

        $this->assertEquals('test', $credentials->username);
        $this->assertEquals('test', $credentials->getCredential('username'));
    }
}
