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
 * Fallback number formatter for dependency-less currency formatting 
 */
class FallbackNumberFormatter
{
    /**
     * Format the given value as currency
     *
     * @param mixed  $value
     * @param string $currency
     *
     * @return string
     */
    public function formatCurrency($value, $currency = 'USD')
    {
        if ($currency !== 'USD') {
            throw new \RuntimeException(sprintf('%s only supports does not support currency "%s". Add symfony/intl to your project for improved language support.', get_class($this), $currency));
        }
        
        return '$' . number_format($value, 2);
    }
}