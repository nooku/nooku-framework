<?php
/**
 * Kodekit - http://timble.net/kodekit
 *
 * @copyright   Copyright (C) 2007 - 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link        https://github.com/timble/kodekit for the canonical source repository
 */

namespace Kodekit\Library;

/**
 * Date Filter
 *
 * Validates or sanitizes a value is an ISO 8601 date string.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Kodekit\Library\Filter
 */
class FilterDate extends FilterTimestamp implements FilterTraversable
{
    /**
     * Validates that a value is an ISO 8601 date string
     *
     * The format is "yyyy-mm-dd".  Also checks to see that the date itself is valid (for example, no Feb 30).
     *
     * @param   mixed   $value Value to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        // Look for Ymd keys?
        if (is_array($value)) {
            $value = $this->_arrayToDate($value);
        }

        // basic date format yyyy-mm-dd
        $expr = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D';

        return (preg_match($expr, $value, $match) && checkdate($match[2], $match[3], $match[1]));
    }

    /**
     * Forces the value to an ISO-8601 formatted date ("yyyy-mm-dd").
     *
     * @param string $value The value to be sanitized.  If an integer, it is used as a Unix timestamp;
     *                      otherwise, converted to a Unix timestamp using [[php::strtotime() | ]].
     * @return  string The sanitized value.
     */
    public function sanitize($value)
    {
         // Look for Ymd keys?
        if (is_array($value)) {
            $value = $this->_arrayToDate($value);
        }

        $result = '0000-00-00';
        if (!(empty($value) || $value == $result))
        {
            $format = 'Y-m-d';

            if (is_numeric($value)) {
                $result = date($format, $value);
            } else {
                $result = date($format, strtotime($value));
            }
        }

        return $result;
    }
}
