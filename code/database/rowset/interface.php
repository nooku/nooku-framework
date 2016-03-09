<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Database Rowset Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database\Rowset
 */
interface KDatabaseRowsetInterface extends KDatabaseRowInterface
{
    /**
     * Insert a new row
     *
     * This function will either clone the row prototype, or create a new instance of the row object for each row
     * being inserted. By default the prototype will be cloned. The row will be stored by it's identity_column if
     * set or otherwise by it's object handle.
     *
     * @param   KDatabaseRowInterface|array $row  A KDatabaseRowInterface object or an array of row properties
     * @param   string  $status     The row status
     * @return  KDatabaseRowsetInterface
     */
    public function insert($row, $status = null);

    /**
     * Find a row in the rowset based on a needle
     *
     * This functions accepts either a know position or associative array of key/value pairs
     *
     * @param   string $needle The position or the key to search for
     * @return KDatabaseRowInterface
     */
    public function find($needle);

    /**
     * Removes a row from the rowset
     *
     * The row will be removed based on it's identity_column if set or otherwise by it's object handle.
     *
     * @param  KObjectHandlable|KDatabaseRowInterface $row
     * @throws \InvalidArgumentException if the object doesn't implement KDatabaseRowInterface
     * @return KDatabaseRowsetAbstract
     */
    public function remove($row);

    /**
     * Checks if the collection contains a specific row
     *
     * @param  KObjectHandlable|KDatabaseRowInterface $row
     * @throws \InvalidArgumentException if the object doesn't implement KDatabaseRowInterface
     * @return  bool Returns TRUE if the object is in the set, FALSE otherwise
     */
    public function contains($row);
}