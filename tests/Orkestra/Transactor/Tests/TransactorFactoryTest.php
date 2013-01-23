<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests;

use Orkestra\Transactor\TransactorFactory;

class TransactorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $transactor = $this->getMockForAbstractClass('Orkestra\Transactor\TransactorInterface');
        $transactor->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('test_transactor'));

        $factory = new TransactorFactory();
        $factory->registerTransactor($transactor);

        $this->assertSame($transactor, $factory->getTransactor('test_transactor'));
        $this->setExpectedException(
            'Orkestra\Transactor\Exception\TransactorException',
            'Unknown Transactor: fake_transactor'
        );

        $factory->getTransactor('fake_transactor');
    }
}
