<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */


/**
 * Module Template Filter
 *
 * This filter allow to dynamically inject data into module position.
 *
 * Filter will parse elements of the form <modules position="[position]">[content]</modules> and prepend or append
 * the content to the module position.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class ComKoowaTemplateFilterModule extends KTemplateFilterAbstract implements KTemplateFilterWrite
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority' => KCommand::PRIORITY_LOW,
        ));

        parent::_initialize($config);
    }

    /**
	 * Find any <module></module> elements and inject them into the JDocument object
	 *
	 * @param string $text Block of text to parse
	 * @return ComKoowaTemplateFilterModule
	 */
    public function write(&$text)
    {
		$matches = array();

		if(preg_match_all('#<module([^>]*)>(.*)</module>#siU', $text, $matches))
		{
		    foreach($matches[0] as $key => $match)
			{
			    //Remove placeholder
			    $text = str_replace($match, '', $text);

			    //Create attributes array
				$attributes = array(
					'style' 	=> 'component',
					'params'	=> '',
					'title'		=> '',
					'class'		=> '',
					'prepend'   => true
				);

		        $attributes = array_merge($attributes, $this->_parseAttributes($matches[1][$key]));

		        //Create module object
			    $module   	       = new KObject();
			    $module->id        = uniqid();
				$module->content   = $matches[2][$key];
				$module->position  = $attributes['position'];
				$module->params    = $attributes['params'];
				$module->showtitle = !empty($attributes['title']);
				$module->title     = $attributes['title'];
				$module->attribs   = $attributes;
				$module->user      = 0;
				$module->module    = 'mod_dynamic';

			    JFactory::getDocument()->modules[$attributes['position']][] = $module;
			}
		}

		return $this;
    }
}

/**
 * Modules Renderer
 *
 * This is a specialised modules renderer which prepends or appends the dynamically created modules
 * to the list of modules before rendering them.
.*
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class JDocumentRendererModules extends JDocumentRenderer
{
    /**
     * Renders a script and returns the results as a string
     *
     * @param   string  $position The name of the element to render
     * @param   array   $params   Array of values
     * @param   string  $content  Override the output of the renderer
     *
     * @return  string  The output of the script
     *
     * @since   11.1
     */
	public function render( $position, $params = array(), $content = null )
	{
        //Get the modules
		$modules = JModuleHelper::getModules($position);

		if(isset($this->_doc->modules[$position]))
		{
		    foreach($this->_doc->modules[$position] as $module)
		    {
		        if($module->attribs['prepend']) {
		            array_push($modules, $module);
		        } else {
		            array_unshift($modules, $module);
		        }
		    }
		}

		//Render the modules
		$renderer = $this->_doc->loadRenderer('module');

		$contents = '';
		foreach ($modules as $module)  {
			$contents .= $renderer->render($module, $params, $content);
		}

		return $contents;
	}
}