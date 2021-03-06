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
 * Dispatcher Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Kodekit\Library\Dispatcher\Context
 */
class DispatcherContext extends ControllerContext implements DispatcherContextInterface
{
    /**
     * The request has been successfully authenticated
     *
     * @return Boolean
     */
    public function isAuthentic()
    {
        return (bool) ObjectConfig::get('authentic', $this->getUser()->isAuthentic(true));
    }

    /**
     * Sets the request as authenticated
     *
     * @return $this
     */
    public function setAuthentic()
    {
        ObjectConfig::set('authentic', true);
        return $this;
    }
}