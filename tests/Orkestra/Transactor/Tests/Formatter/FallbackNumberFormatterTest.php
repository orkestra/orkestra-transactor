<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Tests\Formatter;

use Orkestra\Transactor\Formatter\FallbackNumberFormatter;

/**
 * Unit tests for FallbackNumberFormatter
 *
 * @group orkestra
 * @group transactor
 * @group formatter
 */
class FallbackNumberFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatter()
    {
        $formatter = new FallbackNumberFormatter();
        
        $this->assertEquals('$100.00', $formatter->formatCurrency(100));
    }

    public function testOnlySupportsUsd()
    {
        $formatter = new FallbackNumberFormatter();

        $this->setExpectedException('RuntimeException', 'Orkestra\Transactor\Formatter\FallbackNumberFormatter only supports does not support currency "EUR". Add symfony/intl to your project for improved language support.');
        
        $formatter->formatCurrency(100, 'EUR');
    }
}
