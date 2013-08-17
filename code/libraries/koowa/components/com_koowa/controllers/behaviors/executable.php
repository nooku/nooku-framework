<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */


/**
 * Executable Controller Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class ComKoowaControllerBehaviorExecutable extends KControllerBehaviorExecutable
{
 	/**
     * Command handler
     *
     * @param   string          $name    The command name
     * @param   KCommandContext	$context A command context object
     * @throws  KControllerExceptionForbidden
     * @return  boolean  Can return both true or false.
     */
    public function execute( $name, KCommandContext $context)
    {
        $parts = explode('.', $name);

        if($parts[0] == 'before')
        {
            if(!$this->_checkToken($context))
            {
                $context->setError(new KControllerExceptionForbidden(
                	'Invalid token or session time-out', KHttpResponse::FORBIDDEN
                ));

                return false;
            }
        }

        return parent::execute($name, $context);
    }

    /**
     * Generic authorize handler for controller add actions
     *
     * @return  boolean  Can return both true or false.
     */
    public function canAdd()
    {
        $result = false;

        if(parent::canAdd()) {
            $result = JFactory::getUser()->authorise('core.create') === true;
        }

        return $result;
    }

    /**
     * Generic authorize handler for controller edit actions
     *
     * @return  boolean  Can return both true or false.
     */
    public function canEdit()
    {
        $result = false;

        if(parent::canEdit()) {
            $result = JFactory::getUser()->authorise('core.edit') === true;
        }

        return $result;
    }

    /**
     * Generic authorize handler for controller delete actions
     *
     * @return  boolean  Can return both true or false.
     */
    public function canDelete()
    {
        $result = false;

        if(parent::canDelete()) {
            $result = JFactory::getUser()->authorise('core.delete') === true;
        }

        return $result;
    }

    /**
     * Check if user can perform administrative tasks such as changing configuration options
     *
     * @return  boolean  Can return both true or false.
     */
    public function canAdmin()
    {
        $component = $this->getIdentifier()->package;
        return JFactory::getUser()->authorise('core.admin', 'com_'.$component) === true;
    }

    /**
     * Check if user can can access a component in the administrator backend
     *
     * @return  boolean  Can return both true or false.
     */
    public function canManage()
    {
        $component = $this->getIdentifier()->package;
        return JFactory::getUser()->authorise('core.manage', 'com_'.$component) === true;
    }

	/**
	 * Check the token to prevent CSRF exploits
	 *
     * @param   KCommandContext	$context A command context object
	 * @return  boolean Returns FALSE if the check failed. Otherwise TRUE.
	 */
    protected function _checkToken(KCommandContext $context)
    {
        //Check the token
        if($context->caller->isDispatched())
        {
            $method = KRequest::method();

            //Only check the token for PUT, DELETE and POST requests
            if(($method != KHttpRequest::GET) && ($method != KHttpRequest::OPTIONS))
            {
                if( KRequest::token() !== JSession::getFormToken()) {
                    return false;
                }
            }
        }

        return true;
    }
}
