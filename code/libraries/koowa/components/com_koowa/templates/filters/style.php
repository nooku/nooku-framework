<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */


/**
 * Style Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class ComKoowaTemplateFilterStyle extends KTemplateFilterStyle
{
    /**
     * Render style information
     *
     * @param string    $style  The style information
     * @param boolean   $link   True, if the style information is a URL
     * @param array     $attribs Associative array of attributes
     * @return string
     */
    protected function _renderStyle($style, $link, $attribs = array())
    {
        if(KRequest::type() == 'AJAX') {
            return parent::_renderStyle($style, $link, $attribs);
        }

        $document = JFactory::getDocument();

        if($link) {
            $document->addStyleSheet($style, 'text/css', null, $attribs);
        } else {
            $document->addStyleDeclaration($style);
        }
    }
}
