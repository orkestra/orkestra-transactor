<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Formatter;

/**
 * Domain specific currency formatter, converting cents into dollars
 * 
 * Use this CurrencyFormatter to format transaction amounts and other financial
 * values stored as integers representing cents.
 */
class CurrencyFormatter
{
    /**
     * @var \NumberFormatter
     */
    private $formatter;

    /**
     * @param \NumberFormatter $formatter
     */
    public function __construct($formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Format the given value
     * 
     * @param mixed  $value
     * @param string $currency
     *
     * @return string
     */
    public function format($value, $currency = 'USD')
    {
        return $this->formatter->formatCurrency($value / 100, $currency);
    }
}