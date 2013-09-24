<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Json View
 *
 * The JSON view implements supports for JSONP through the model's callback state. If a callback is present the content
 * will be padded.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\View
 */
class KViewJson extends KViewAbstract
{
    /**
     * The padding for JSONP
     *
     * @var string
     */
    protected $_padding;

    /**
     * JSON API version
     *
     * @var string
     */
    protected $_version;

    /**
     * A list of text fields in the row
     *
     * URLs will be converted to fully qualified ones in these fields.
     *
     * @var string
     */
    protected $_text_fields;

    /**
     * True if the view is for a plural resource
     *
     * @var boolean
     */
    protected $_plural;

    /**
     * Constructor
     *
     * @param   KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        if(empty($config->padding) && $config->padding !== false)
        {
            $state = $this->getModel()->getState();

            if(isset($state->callback) && (strlen($state->callback) > 0)) {
                $config->padding = $state->callback;
            }
        }

        $this->_padding = $config->padding;
        $this->_version = $config->version;
        $this->_plural  = $config->plural;

        $this->_text_fields = KObjectConfig::unbox($config->text_fields);
    }

    /**
     * Initializes the config for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'padding'	  => '', // Can be turned off by setting this to false
            'version'     => '1.0',
            'text_fields' => array('description'), // Links are converted to absolute ones in these fields
            'plural'      => KStringInflector::isPlural($this->getName())
        ))->append(array(
            'mimetype' => 'application/json; version=' . $config->version,
        ));

        parent::_initialize($config);
    }

    /**
     * Return the views content
     *
     * If the view 'content'  is empty the content will be generated based on the model data, if it set it will
     * be returned instead.
     *
     * If the model contains a callback state, the callback value will be used to apply padding to the JSON output.
     *
     *  @return string A RFC4627-compliant JSON string, which may also be embedded into HTML.
     */
    public function display()
    {
        if (empty($this->_content))
        {
            $this->_content = $this->_renderData();
            $this->_processLinks($this->_content);
        }

        //Serialise
        if (!is_string($this->_content))
        {
            // Root should be JSON object, not array
            if (is_array($this->_content) && count($this->_content) === 0) {
                $this->_content = new ArrayObject();
            }

            // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
            $this->_content = json_encode($this->_content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        }

        //Handle JSONP
        if (!empty($this->_padding)) {
            $this->_content = $this->_padding.'('.$this->_content.');';
        }

        return parent::display();
    }

    /**
     * Force the route to fully qualified and not escaped by default
     *
     * @param   string  $route   The query string used to create the route
     * @param   boolean $fqr     If TRUE create a fully qualified route. Default TRUE.
     * @param   boolean $escape  If TRUE escapes the route for xml compliance. Default FALSE.
     * @return  string  The route
     */
    public function createRoute($route = '', $fqr = true, $escape = false)
    {
        return parent::createRoute($route, $fqr, $escape);
    }

    /**
     * Returns the JSON data
     *
     * It converts relative URLs in the content to relative before returning the result
     *
     * @return array
     */
    protected function _renderData()
    {
        $model  = $this->getModel();
        $data   = $this->_getList($model->getList());
        $output = array(
            'version' => $this->_version,
            'links' => array(
                'self' => array(
                    'href' => $this->_getPageLink(),
                    'type' => $this->mimetype
                )
            ),
            'entities' => $data
        );

        if ($this->_plural)
        {
            $total  = $model->getTotal();
            $limit  = (int) $model->limit;
            $offset = (int) $model->offset;

            $output['meta'] = array(
                'offset'   => $offset,
                'limit'    => $limit,
                'total'	   => $total
            );

            if ($limit && $total-($limit + $offset) > 0)
            {
                $output['links']['next'] = array(
                    'href' => $this->_getPageLink(array('offset' => $limit+$offset)),
                    'type' => $this->mimetype
                );
            }

            if ($limit && $offset && $offset >= $limit)
            {
                $output['links']['previous'] = array(
                    'href' => $this->_getPageLink(array('offset' => max($offset-$limit, 0))),
                    'type' => $this->mimetype
                );
            }
        }

        return $output;
    }

    /**
     * Returns the JSON representation of a rowset
     *
     * @param  KDatabaseRowsetInterface $rowset
     * @return array
     */
    protected function _getList(KDatabaseRowsetInterface $rowset)
    {
        $result = array();

        foreach ($rowset as $row) {
            $result[] = $this->_getItem($row);
        }

        return $result;
    }

    /**
     * Get the item data
     *
     * @param KDatabaseRowInterface  $row   Document row
     * @return array The array with data to be encoded to json
     */
    protected function _getItem(KDatabaseRowInterface $row)
    {
        $method = '_get'.ucfirst($row->getIdentifier()->name);

        if ($method !== '_getItem' && method_exists($this, $method)) {
            $data = $this->$method($row);
        } else {
            $data = $row->toArray();
        }

        if (!isset($data['links'])) {
            $data['links'] = array();
        }

        if (!isset($data['links']['self']))
        {
            $data['links']['self'] = array(
                'href' => $this->_getItemLink($row),
                'type' => $this->mimetype
            );
        }

        return $data;
    }

    /**
     * Get the item link
     *
     * @param KDatabaseRowInterface  $row
     * @return string
     */
    protected function _getItemLink(KDatabaseRowInterface $row)
    {
        $package = $this->getIdentifier()->package;
        $view    = $row->getIdentifier()->name;

        return $this->createRoute(sprintf('option=com_%s&view=%s&slug=%s&format=json', $package, $view, $row->slug));
    }

    /**
     * Get the page link
     *
     * @param  array  $query Additional query parameters to merge
     * @return string
     */
    protected function _getPageLink(array $query = array())
    {
        $url = KRequest::url();

        if ($query) {
            $url->setQuery(array_merge($url->getQuery(true), $query));
        }

        return (string) $url;
    }

    /**
     * Converts links in an array from relative to absolute
     *
     * @param array $array Source array
     */
    protected function _processLinks(array &$array)
    {
        $base = KRequest::url()->toString(KHttpUrl::AUTHORITY);

        foreach ($array as $key => &$value)
        {
            if (is_array($value)) {
                $this->_processLinks($value);
            }
            elseif ($key === 'href')
            {
                if (substr($value, 0, 4) !== 'http') {
                    $array[$key] = $base.$value;
                }
            }
            elseif (in_array($key, $this->_text_fields)) {
                $array[$key] = $this->_processText($value);
            }
        }
    }

    /**
     * Convert links in a text from relative to absolute and runs them through JRoute
     *
     * @param string $text The text processed
     * @return string Text with converted links
     */
    protected function _processText($text)
    {
        $base    = KRequest::url()->toString(KHttpUrl::AUTHORITY);
        $matches = array();

        preg_match_all("/(href|src)=\"(?!http|ftp|https|mailto|data)([^\"]*)\"/", $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $text = str_replace($match[0], $match[1].'="'.$base.JRoute::_($match[2]).'"', $text);
        }

        return $text;
    }
}
