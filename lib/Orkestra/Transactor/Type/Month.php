<?php

namespace Orkestra\Transactor\Type;

use Orkestra\Common\Type\TypeBase;

/**
 * Represents a single month of the year
 */
class Month extends TypeBase
{
    /**
     * {@inheritdoc}
     */
    protected function _validate($value)
    {
        return ($value > 0 && $value < 13) ? true : false;
    }

    /**
     * To String
     *
     * @see getLongMonth
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLongName();
    }

    /**
     * Get Short Month
     *
     * @return string The month number with no leading zeros
     */
    public function getShortMonth()
    {
        return date('n', mktime(0, 0, 0, $this->_value));
    }

    /**
     * Get Long Month
     *
     * @return string The month number with leading zeros
     */
    public function getLongMonth()
    {
        return date('m', mktime(0, 0, 0, $this->_value));
    }

    /**
     * Get Short Name
     *
     * @return string The three letter name of the month
     */
    public function getShortName()
    {
        return date('M', mktime(0, 0, 0, $this->_value));
    }

    /**
     * Get Long Name
     *
     * @return string The full month name
     */
    public function getLongName()
    {
        return date('F', mktime(0, 0, 0, $this->_value));
    }
}
