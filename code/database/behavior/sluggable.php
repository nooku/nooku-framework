<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Sluggable Database Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database\Behavior
 */
class KDatabaseBehaviorSluggable extends KDatabaseBehaviorAbstract
{
    /**
     * The column name from where to generate the slug, or a set of column names to concatenate for generating the slug.
     *
     * Default is 'title'.
     *
     * @var array
     */
    protected $_columns;

    /**
     * Separator character / string to use for replacing non alphabetic characters in generated slug.
     *
     * Default is '-'.
     *
     * @var string
     */
    protected $_separator;

    /**
     * Maximum length the generated slug can have. If this is null the length of the slug column will be used.
     *
     * Default is NULL.
     *
     * @var integer
     */
    protected $_length;

    /**
     * Set to true if slugs should be re-generated when updating an existing row.
     *
     * Default is true.
     *
     * @var boolean
     */
    protected $_updatable;

    /**
     * Set to true if slugs should be unique. If false and the slug column has a unique index set this will result in
     * an error being throw that needs to be recovered.
     *
     * Default is NULL.
     *
     * @var boolean
     */
    protected $_unique;

    /**
     * Constructor.
     *
     * @param   KObjectConfig $config Configuration options
     */
    public function __construct( KObjectConfig $config = null)
    {
        parent::__construct($config);

        $this->_columns   = (array) KObjectConfig::unbox($config->columns);
        $this->_separator = $config->separator;
        $this->_updatable = $config->updatable;
        $this->_length    = $config->length;
        $this->_unique    = $config->unique;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'columns'   => 'title',
            'separator' => '-',
            'updatable' => true,
            'length'    => null,
            'unique'    => null
        ));

        parent::_initialize($config);
    }

    /**
     * Check if the behavior is supported
     *
     * Behavior requires a 'slug' row property
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $result = true;
        $table  = $this->getMixer();

        //Only check if we are connected with a table object, otherwise just return true.
        if($table instanceof KDatabaseTableInterface)
        {
            if($table->hasColumn('slug'))
            {
                //If unique is NULL use the column metadata
                if(is_null($this->_unique)) {
                    $this->_unique = $table->getColumn('slug', true)->unique;
                }
            }
            else $result = false;
        }

        return $result;
    }

    /**
     * Get the canonical slug
     *
     * This function will always return a unique and canonical slug. If the slug is not unique it will prepend the
     * identity column value.
     *
     * @link : https://en.wikipedia.org/wiki/Canonicalization
     *
     * @return string
     */
    public function getSlug()
    {
        if (!$this->_unique)
        {
            $column = $this->getIdentityColumn();
            $result = $this->{$column} . $this->_separator . $this->slug;
        }
        else $result = $this->slug;

        return $result;
    }

    /**
     * Insert a slug
     *
     * If multiple columns are set they will be concatenated and separated by the separator in the order they are
     * defined.
     *
     * Requires a 'slug' column
     *
     * @param  KDatabaseContextInterface $context
     * @return void
     */
    protected function _beforeInsert(KDatabaseContextInterface $context)
    {
        $this->_createSlug();
    }

    /**
     * Update the slug
     *
     * Only works if {@link $updatable} property is TRUE. If the slug is empty the slug will be regenerated. If the
     * slug has been modified it will be sanitized.
     *
     * Requires a 'slug' column
     *
     * @param  KDatabaseContextInterface $context
     * @return void
     */
    protected function _beforeUpdate(KDatabaseContextInterface $context)
    {
        if($this->_updatable) {
            $this->_createSlug();
        }
    }

    /**
     * Create the slug
     *
     * @return void
     */
    protected function _createSlug()
    {
        //Regenerate the slug
        if($this->isModified('slug')) {
            $this->slug = $this->_createFilter()->sanitize($this->slug);
        }

        //Handle empty slug
        if(empty($this->slug))
        {
            $slugs = array();
            foreach($this->_columns as $column) {
                $slugs[] = $this->_createFilter()->sanitize($this->$column);
            }

            $this->slug = implode($this->_separator, array_filter($slugs));
        }

        //Canonicalize the slug
        if($this->_unique) {
            $this->_canonicalizeSlug();
        }
    }

    /**
     * Create a sluggable filter
     *
     * @return KFilterSlug
     */
    protected function _createFilter()
    {
        $config = array();
        $config['separator'] = $this->_separator;

        if (!isset($this->_length)) {
            $config['length'] = $this->getTable()->getColumn('slug')->length;
        } else {
            $config['length'] = $this->_length;
        }

        //Create the filter
        $filter = $this->getObject('lib:filter.slug', $config);
        return $filter;
    }

    /**
     * Make sure the slug is unique
     *
     * This function checks if the slug already exists and if so appends a number to the slug to make it unique.
     * The slug will get the form of slug-x.
     *
     * @return void
     */
    protected function _canonicalizeSlug()
    {
        $table = $this->getTable();

        //If the slug needs to be unique and it already exists, make it unique
        $query = $this->getObject('lib:database.query.select');
        $query->where('slug = :slug')->bind(array('slug' => $this->slug));

        if (!$this->isNew())
        {
            $query->where($table->getIdentityColumn().' <> :id')
                ->bind(array('id' => $this->id));
        }

        if($table->count($query))
        {
            $length = $this->_length ? $this->_length : $table->getColumn('slug')->length;

            // Cut 4 characters to make space for slug-1 slug-23 etc
            if ($length && strlen($this->slug) > $length-4) {
                $this->slug = substr($this->slug, 0, $length-4);
            }

            $query = $this->getObject('lib:database.query.select')
                ->columns('slug')
                ->where('slug LIKE :slug')
                ->bind(array('slug' => $this->slug . '-%'));

            $slugs = $table->select($query, KDatabase::FETCH_FIELD_LIST);

            $i = 1;
            while(in_array($this->slug.'-'.$i, $slugs)) {
                $i++;
            }

            $this->slug = $this->slug.'-'.$i;
        }
    }
}
