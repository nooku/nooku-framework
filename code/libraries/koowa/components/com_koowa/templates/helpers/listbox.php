<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */


/**
 * Listbox Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class ComKoowaTemplateHelperListbox extends ComKoowaTemplateHelperSelect
{    
    /**
     * Generates an HTML enabled listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function enabled( $config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'      => 'enabled',
            'attribs'   => array(),
            'deselect'  => true,
            'prompt'    => '- '.$this->translate('Select').' -',
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $options = array();

        if($config->deselect) {
            $options[] = $this->option(array('text' => $config->prompt, 'value' => ''));
        }

        $options[] = $this->option(array('text' => $this->translate( 'Enabled' ) , 'value' => 1 ));
        $options[] = $this->option(array('text' => $this->translate( 'Disabled' ), 'value' => 0 ));

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Generates an HTML published listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function published($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'      => 'enabled',
            'attribs'   => array(),
            'deselect'  => true,
            'prompt'    => '- '.$this->translate('Select').' -'
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));
    
        $options = array();
    
        if ($config->deselect) {
            $options[] = $this->option(array('text' => $config->prompt, 'value' => ''));
        }
    
        $options[] = $this->option(array('text' => $this->translate('Published'), 'value' => 1 ));
        $options[] = $this->option(array('text' => $this->translate('Unpublished') , 'value' => 0 ));
    
        //Add the options to the config object
        $config->options = $options;
    
        return $this->optionlist($config);
    }

    /**
     * Generates an HTML access listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function access($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'      => 'access',
            'attribs'   => array(),
            'deselect'  => true,
            'prompt'    => '- '.$this->translate('Select').' -'
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $prompt = false;
        if ($config->deselect) {
            $prompt = array((object) array('value' => '', 'text'  => $config->prompt));
        }

        $html = JHtml::_('access.level', $config->name, $config->selected, $config->attribs->toArray(), $prompt);
    
        return $html;
    }
    /**
     * Generates an HTML optionlist based on the distinct data from a model column.
     *
     * The column used will be defined by the name -> value => column options in cascading order.
     *
     * If no 'model' name is specified the model identifier will be created using
     * the helper identifier. The model name will be the pluralised package name.
     *
     * If no 'value' option is specified the 'name' option will be used instead.
     * If no 'text'  option is specified the 'value' option will be used instead.
     *
     * @param 	array 	$config An optional array with configuration options
     * @return	string	Html
     * @see __call()
     */
    protected function _render($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'autocomplete' => false
        ));

        if($config->autocomplete) {
            $result = $this->_autocomplete($config);
        } else {
            $result = $this->_listbox($config);
        }

        return $result;
    }

    /**
     * Generates an HTML optionlist based on the distinct data from a model column.
     *
     * The column used will be defined by the name -> value => column options in
     * cascading order.
     *
     * If no 'model' name is specified the model identifier will be created using
     * the helper identifier. The model name will be the pluralised package name.
     *
     * If no 'value' option is specified the 'name' option will be used instead.
     * If no 'text'  option is specified the 'value' option will be used instead.
     *
     * @param 	array|KConfig 	$config An optional array with configuration options
     * @return	string	Html
     * @see __call()
     */
    protected function _listbox($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'		  => '',
            'attribs'	  => array(),
            'model'		  => KInflector::pluralize($this->getIdentifier()->package),
            'deselect'    => true,
            'prompt'      => '- Select -',
            'unique'	  => true
        ))->append(array(
                'value'		 => $config->name,
                'selected'   => $config->{$config->name},
                'identifier' => 'com://'.$this->getIdentifier()->application.'/'.$this->getIdentifier()->package.'.model.'.KInflector::pluralize($config->model)
            ))->append(array(
                'text'		=> $config->value,
            ))->append(array(
                'filter' 	=> array('sort' => $config->text),
            ));

        $list = $this->getService($config->identifier)->set($config->filter)->getList();

        //Get the list of items
        $items = $list->getColumn($config->value);
        if($config->unique) {
            $items = array_unique($items);
        }

        //Compose the options array
        $options   = array();
        if($config->deselect) {
            $options[] = $this->option(array('text' => $this->translate($config->prompt)));
        }

        foreach($items as $key => $value)
        {
            $item      = $list->find($key);
            $options[] =  $this->option(array('text' => $item->{$config->text}, 'value' => $item->{$config->value}));
        }

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Renders a listbox with autocomplete behavior
     *
     * @see    KTemplateHelperBehavior::autocomplete
     *
     * @param  array|KConfig    $config
     * @return string	The html output
     */
    protected function _autocomplete($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'name'		 => '',
            'attribs'	 => array(),
            'model'		 => KInflector::pluralize($this->getIdentifier()->package),
            'validate'   => true,
        ))->append(array(
                'value'		 => $config->name,
                'selected'   => $config->{$config->name},
                'identifier' => 'com://'.$this->getIdentifier()->application.'/'.$this->getIdentifier()->package.'.model.'.KInflector::pluralize($config->model)
            ))->append(array(
                'text'		=> $config->value,
            ))->append(array(
                'filter' 	=> array(),
            ));

        //For the autocomplete behavior
        $config->element = $config->value;
        $config->path    = $config->text;

        $html = $this->getTemplate()->getHelper('behavior')->autocomplete($config);

        return $html;
    }

    /**
     * Search the mixin method map and call the method or trigger an error
     *
     * This function check to see if the method exists in the mixing map if not it will call the 'listbox' function.
     * The method name will become the 'name' in the config array.
     *
     * This can be used to auto-magically create select filters based on the function name.
     *
     * @param  string   $method The function name
     * @param  array    $arguments The function arguments
     * @throws BadMethodCallException   If method could not be found
     * @return mixed The result of the function
     */
    public function __call($method, $arguments)
    {
        if(!in_array($method, $this->getMethods()))
        {
            $config = $arguments[0];
            $config['name']  = KInflector::singularize(strtolower($method));

            return $this->_render($config);
        }

        return parent::__call($method, $arguments);
    }
}