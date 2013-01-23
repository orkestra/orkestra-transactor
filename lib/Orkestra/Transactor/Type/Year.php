<?php

/*
 * This file is part of the Orkestra Transactor package.
 *
 * Copyright (c) Orkestra Community
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Orkestra\Transactor\Type;

use Orkestra\Common\Type\AbstractType;

/**
 * Represents a single year
 */
class Year extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    protected function validate($value)
    {
        return ($value > 1000 && $value < 9999) ? true : false;
    }

    /**
     * To String
     *
     * @see getLongYear
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLongYear();
    }

    /**
     * Get Long Year
     *
     * Returns the entire 4 digit year
     *
     * @return string
     */
    public function getLongYear()
    {
        return (string)$this->value;
    }

    /**
     * Get Short Year
     *
     * Returns the short 2 digit year
     *
     * @return string
     */
    public function getShortYear()
    {
        return substr($this->value, 2);
    }
}
