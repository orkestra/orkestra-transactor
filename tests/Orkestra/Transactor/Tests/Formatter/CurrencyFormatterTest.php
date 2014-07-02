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

use Orkestra\Transactor\Formatter\CurrencyFormatter;
use Orkestra\Transactor\Formatter\FallbackNumberFormatter;

/**
 * Unit tests for CurrencyFormatter
 *
 * @group orkestra
 * @group transactor
 * @group formatter
 */
class CurrencyFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFallbackFormatter()
    {
        $formatter = new CurrencyFormatter(new FallbackNumberFormatter());

        $this->assertEquals('$5.00', $formatter->format(500));
    }

    public function testIntlFormatter()
    {
        $formatter = new CurrencyFormatter(new \NumberFormatter('en', \NumberFormatter::CURRENCY));

        $this->assertEquals('$5.00', $formatter->format(500));
        $this->assertEquals('€5.00', $formatter->format(500, 'EUR'));
    }
}
