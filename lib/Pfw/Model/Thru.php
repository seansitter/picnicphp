<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Model_Thru
{
    protected $_thru_from_class = null;
    protected $_thru_as_name = null;

    protected $_thru_from_instance = null;
    protected $_thru_desc = null;

    public function __construct($from_class, $as, $desc = null) {
        $this->_thru_from_class = $from_class;
        $this->_thru_as_name = $as;
        $this->_thru_desc = $desc;
    }

    public function associate($from_object, $to_object)
    {
        $desc = $this->_getAssociationDescription();

        // left table's key
        $from_key = $desc['my_key'];
        // join table's left key
        $from_join_key = $desc['my_join_key'];
        // join table's right key
        $as_join_key = $desc['as_join_key'];
        // right table's key
        $as_key = $desc['as_key'];

        // value of left key
        $from_key_value = $from_object->$from_key;
        // value or right key
        $as_key_value = $to_object->$as_key;

        // assign link
        $this->$from_join_key = $from_key_value;
        $this->$as_join_key = $as_key_value;

        $table = $desc['thru_table'];

        // save link
        $db = $from_object->_getDb();
        $db->insertIntoTable(
            $table,
            array(
                $from_join_key => $from_key_value,
                $as_join_key => $as_key_value
            )
        );
    }

    public function delete()
    {
        $db = $this->_getDb();
        $desc = $this->_getAssociationDescription();
        $table = $desc['thru_table'];
        $from_join_key = $desc['my_join_key'];
        $as_join_key = $desc['as_join_key'];

        $db->deleteFromTable(
            $table,
            array(
                "{$from_join_key} = %s AND {$as_join_key} = %s",
                $this->$from_join_key,
                $this->$as_join_key
            )
        );
    }

    public function updateDbProperty($name, $value)
    {
        $db = $this->_getDb();
        $desc = $this->_getAssociationDescription();
        $table = $desc['thru_table'];
        $from_join_key = $desc['my_join_key'];
        $as_join_key = $desc['as_join_key'];

        $db->updateTable(
            $table,
            array($name => $value),
            array(
                "{$from_join_key} = %s AND {$as_join_key} = %s",
                $this->$from_join_key,
                $this->$as_join_key
            )
        );
    }

    public function setProperties($properties)
    {
        if (empty($properties)) { return; }
        while (list($key, $value) = each($properties)) {
            if (is_valid_prop($key)) {
                $this->$key = $value;
            }
        }
    }

    protected function _getEmptyFromInstance()
    {
        if (!is_null($this->_thru_from_instance)) {
            return $this->_thru_from_instance;
        }
        $class = $this->_thru_from_class;
        return new $class();
    }

    protected function _getFromInstance()
    {
        if (is_null($this->_thru_from_instance)) {
            $this->_thru_from_instance = $this->_getEmptyFromInstance();
            $desc = $instance->_getAssociationDescription($this->_thru_as_name);
            $from_key = $desc['my_key'];
            $from_join_key = $desc['my_join_key'];
            $this->_thru_from_instance->$from_key = $this->$from_join_key;
        }

        return $this->_thru_from_instance;
    }

    protected function _getAssociationDescription()
    {
        if (is_null($this->_thru_desc)) {
            $this->_thru_desc = $this->_getEmptyFromInstance()->_getAssociationDescription();
        }
        return $this->_thru_desc;
    }

    protected function _getDb()
    {
        return $this->_getFromInstance()->_getDb();
    }
}
