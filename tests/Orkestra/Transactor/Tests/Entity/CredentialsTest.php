<?php

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
