<?php

namespace Orkestra\Transactor\Type;

use Orkestra\Common\Type\TypeBase;

/**
 * Represents a single year
 */
class Year extends TypeBase
{
    /**
     * {@inheritdoc}
     */
    protected function _validate($value)
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
        return (string)$this->_value;
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
        return substr($this->_value, 2);
    }
}
