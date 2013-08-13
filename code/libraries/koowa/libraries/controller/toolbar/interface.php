<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Controller Toolbar Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Controller
 */
interface KControllerToolbarInterface
{
    /**
     * Get the controller object
     *
     * @return  KControllerInterface
     */
    public function getController();

    /**
     * Get the toolbar's name
     *
     * @return string
     */
    public function getName();

    /**
     * Add a command
     *
     * @param   string	$name   The command name
     * @param	mixed	$config Parameters to be passed to the command
     * @return  KControllerToolbarInterface
     */
    public function addCommand($name, $config = array());

 	/**
     * Get the list of commands
     *
     * @return  array
     */
    public function getCommands();
}
