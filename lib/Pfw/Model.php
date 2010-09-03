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

Pfw_Loader::loadClass("Pfw_Exception_NotRetrieved");
Pfw_Loader::loadClass("Pfw_Exception_NotFound");
Pfw_Loader::loadClass('Pfw_Exception_Model');
Pfw_Loader::loadClass("Pfw_Model_QueryObject");
Pfw_Loader::loadClass('Pfw_Db');

/**
 * This is the base class for all models with the project. Defines basic
 * CRUD methods as well as methods which describe the nature of the
 * associations defined by instances of subclasses.
 * 
 * @category      Framework
 * @package       Pfw
 */
abstract class Pfw_Model
{
    const ASSOC_ONE = 'o';
    const ASSOC_MANY = 'm';
    const ASSOC_BELONGSTO = 'b';
    const ASSOC_HABTM = "bm";

    const SAVE_INSERT = 1;
    const SAVE_UPDATE = 2;

    protected static $_description = array();
    protected $_errors = null;
    protected $_is_retrieved = false;
    protected $_have_run_assoc_setup = false;
    protected $_db = null;
    
    private $_reserved_fields = array(
        '_description', 
        '_is_retrieved',
        '_have_run_assoc_setup',
        '_db', 
        '_reserved_fields'
    );

    public function __construct($id = null)
    {
        $cls = get_class($this);

        // all class initializations should occur here the class
        // try to do as few function invocations as possible
        if (false == self::$_description[$cls]['initialized']) {
            if (method_exists($this, 'setup')) {
                $this->setup();
            }
            if (!self::$_description[$cls]['primary_key']) {
                self::$_description[$cls]['primary_key'] = 'id';
            }
            self::$_description[$cls]['initialized'] = true;
        }

        if (null !== $id) {
            $pk = self::$_description[$cls]['primary_key'];
            $this->$pk = $id;
        }
    }
    
    /**
     * Determines if this class has $property, even if it is set to null.
     * 
     * @param string $property
     * @return bool
     */
    public function hasProperty($property) 
    {
    	return array_key_exists($property, get_object_vars($this)) ? true : false;
    }
    
    /**
     * Returns true if all properties of this model instance
     * are equal to all properties of other model instance.
     * 
     * @param Pfw_Model $model
     * @return bool
     */
    public function equals($model) {
        if (!is_a($model, 'Pfw_Model')) {
            return false;
        }
        
        if ($this->getClass() != $model->getClass()) {
            return false;
        }
        
        $my_props = get_object_vars($this);
        $my_reserved = $this->_reserved_fields;
        $my_test_props = array();
        foreach ($my_props as $prop => $value) {
            if (!in_array($prop, $my_reserved)) {
                $my_test_props[$prop] = $value;
            }
        }
        
        $other_props = get_object_vars($model);
        $other_reserved = $model->_reserved_fields;
        $other_test_props = array();
        foreach ($other_props as $prop => $value) {
            if (!in_array($prop, $other_reserved)) {
                $other_test_props[$prop] = $value;
            }
        }
        $diff = array_diff($my_test_props, $other_test_props);
        
        if (empty($diff)) {
            return true;
        }
        
        return false;
    }


    /**
     * Gets the value of the primary key for this model.
     *
     * @return mixed
     */
    public function getId()
    {
        $pk = $this->getPrimaryKey();
        return $this->$pk;
    }


    /**
     * Sets the value of the primary key for this model.
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $pk = $this->getPrimaryKey();
        $this->$pk = $id;
    }


    /**
     * If this instance has a value for its primary key property,
     * sets our instance variables to the values in the db.
     *
     * @example Pfw/Model/retrieve.inc
     *
     * @param $options
     * @return unknown_type
     */
    public function retrieve($options = array())
    {
        if(null == $this->getId()){
            throw new Pfw_Exception_System("Cannot retrieve '".$this->getClass()."' without an id");
        }

        $pk_name = $this->getPrimaryKey();
        $qo = $this->Q($this->_getDb())->where(array("this.{$pk_name} = %s", $this->getId()))->first();

        if (isset($options['with'])) {
            if (is_string($options['with'])) {
                $desc = $this->_getAssociationDescription($options['with']);
                $js = isset($desc['default_strategy']) ? $desc['default_strategy'] : "Immediate";
                $qo->with($options['with'], array('join_strategy' => $js));
            } elseif(is_array($options['with'])) {
                $withs = array_to_hash($options['with']);
                foreach ($withs as $with => $options) {
                    $desc = $this->_getAssociationDescription($with);
                    if ($with == $options) {
                        $js = isset($desc['default_strategy']) ? $desc['default_strategy'] : "Immediate";
                        $qo->with($with, array('join_strategy' => $js));
                    } else {
                        if (isset($options['join_strategy'])) {
                            $js = $options['join_strategy'];
                        } else {
                            $js = isset($desc['default_strategy']) ? $desc['default_strategy'] : "Immediate";
                        }
                        $qo->with($with, array('join_strategy' => $js));
                    }
                }
            }
        }

        $obj = $qo->exec();

        if (is_null($obj)) {
            Pfw_Loader::loadClass('Pfw_Exception_NotFound');
            $class = $this->getClass();
            throw new Pfw_Exception_NotFound("Could not find '$class' with id: ".$this->getId());
        }

        $this->_setProperties((array)$obj, false);
        $this->_setRetrieved();
        unset($obj);
    }


    /**
     * If the field doesn't exist within this model, fetch it if its an association.
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($this->_definesAssociation($name)) {
            $this->fetchAssociation($name);
            return $this->$name;
        }
        return null;
    }


    /**
     * Fetches the given association for this instance.
     *
     * @param string $name the name of the association
     * @param array $params
     * @return mixed
     */
    public function fetchAssociation($name, $params = array())
    {
        $desc = $this->_getAssociationDescription($name);
        $strategy_class = $desc['default_strategy'];
        if ($strategy_class == "Immediate") {
            $strategy_class = "Pfw_Associate_PostQuery";
        }
        Pfw_Loader::loadClass($strategy_class);
        $strategy = new $strategy_class();

        return $strategy->exec($this, $name, $params);
    }


    /**
     * Calls the insert method if this object has a primary key,
     * else calls update.
     *
     * @return integer
     */
    public function save($options = array())
    {
        $pk = $this->getPrimaryKey();
        if(isset($this->$pk)){
            return $this->update($options);
        }
        return $this->insert($options);
    }
    
    /**
     * Updated the db record identified by this instance
     * with the data from this instance.
     *
     * @return bool true if update succeeded, false otherwise
     */
    public function update($options = array())
    {
        $desc = $this->_getDescription();

        if (!empty($options['fields'])) {
            $fields = $options['fields'];
        } else {
            $update_fields = $this->getUpdateFields();
            if (isset($update_fields)) {
                $fields = $update_fields;
            } else {
                $fields = $this->getDbFields();
            }
        }
        
        $my_vars = get_object_vars($this);
        foreach ($fields as $field) {
            if (!in_array($field, $my_vars)) {
                continue;
            }

            if(is_valid_prop($field)){
                $data[$field] = $this->$field;
            } else {
                trigger_error("Field '$field' is not a valid property name, skipping", E_USER_WARNING);
            }
        }

        $this->doSaveFilter($options, $data, Pfw_Model::SAVE_UPDATE);
        if (false === $this->doValidate($options, Pfw_Model::SAVE_UPDATE)) {
            return false;
        }
        
        return $this->updateTable($data);
    }
    
    /**
     * Updates the table for this instance using the data in $data.
     * Does not call beforeSaveFilter or validate.
     * Does not assign the data back to the object as properties.
     * 
     * @param array $data
     * @return bool
     */
    public function updateTable($data) {
        $id = $this->getId();
        if (empty($id)) {
            throw new Pfw_Exception_NotFound("Missing primary key when updating table {$table}");
        }
        $table = $this->getTable();
        $key = $this->getPrimaryKey();
        
        $count = $this->_getDb()->updateTable($table, $data, array("$key = %s", $id));

        return (is_int($count) and ($count >= 0)) ? true : false;
    }
    
    /**
     * Checks the affected rows of the previous operation
     *
     *
     */
    public function affectedCount() {
	# TODO: ...
    }


    /**
     * Inserts the data in this instance into the db.
     *
     * @return mixed the primary key value
     */
    public function insert($options = array())
    {
        $desc = $this->_getDescription();
        
        if (!empty($options['fields'])) {
            $fields = $options['fields'];
        } else {
            $insert_fields = $this->getInsertFields();
            if (!empty($insert_fields)) {
                $fields = $insert_fields;
            } else {
                $fields = $this->getDbFields();
            }
        }
        
        $pk = $this->getPrimaryKey();
        $pk_was_set = false;
        if (isset($this->$pk)) {
            $pk_was_set = true;
        }

        $my_vars = get_object_vars($this);
        foreach ($fields as $field) {
        	if (!array_key_exists($field, $my_vars)) {
                continue;
            }

            if(is_valid_prop($field)){
                $data[$field] = $this->$field;
            } else {
                trigger_error("Field '$field' is not a valid property name, skipping", E_USER_WARNING);
            }
        }
        
        $this->doSaveFilter($options, $data, Pfw_Model::SAVE_INSERT);
        if (false === $this->doValidate($options, Pfw_Model::SAVE_INSERT)) {
        	return false;
        }

        $table = $this->getTable();
        $id = $this->_getDb()->insertIntoTable($table, $data);

        if (($id <= 0) || ($id === false)) {
            return false;
        }

        $this->_setRetrieved();
        if (!$pk_was_set) {
            $this->$pk = $id;
        }

        return $this->$pk;
    }
    
    protected function doSaveFilter($opt, &$data, $save_method) {
        if (isset($opt['filter_method'])) {
            $fm = $opt['filter_method'];
            $me = method_exists($this, $fm);
            if (false == $me) {
            	error_log("filter method '{$fm}' requested on ".get_class($this).
            	  " but method does not exist");
            }
        }
        else {
            $fm = "beforeSaveFilter";
            $me = method_exists($this, $fm);
        }
        if ($me and !(isset($opt['filter']) and ($opt['filter'] == false))) {
            return $this->$fm($data, $save_method);
        }
    }
    
    protected function doValidate($opt, $save_method) {
        if (isset($opt['validate_method'])) {
            $vm = $opt['validate_method'];
            $me = method_exists($this, $vm);
            if (false == $me) {
                error_log("validate method '{$vm}' requested on ".get_class($this).
                  " but method does not exist");
            }
        }
        else {
            $vm = "validate";
            $me = method_exists($this, $vm);
        }
        if ($me and !(isset($opt['validate']) and ($opt['validate'] == false))) { 
            if (false == $this->$vm($save_method)) {
               return false; 
            }
        }
        return true;
    }

    /**
     * Deletes the record identified by this instance from the db.
     * Sets the property representing the primary key to null.
     * Unsets the retrieved flag.
     */
    public function delete()
    {
        $id = $this->getId();
        if (!isset($id)) {
            throw new Pfw_Exception_Model("Primary key must be set when deleteing");
        }

        $desc = $this->_getDescription();

        $pk = $this->getPrimaryKey();
        $table = $this->getTable();
        $this->_getDb()->deleteFromTable($table, array("$pk = %s", $id));

        $this->_unsetRetrieved();
        $this->setId(null);
    }


    /**
     * Sets the properties on this instance when fetched.
     * Fetching is assumed to come from a safe source such 
     * as a db.
     *
     * @param array $properties property => value pairs
     */
    public function fetchSetProperties($properties)
    {
        if (empty($properties)) { return; }
        
        $af_fields = $this->getAfFields();
        
        $this->_setProperties($properties);
    }
    
    
    /**
     * Gets auto-filtered fields
     */
    protected function getAfFields()
    {
        $my_class = $this->getClass();
        if (isset(self::$_description[$my_class]['af_props'])) {
            return self::$_description[$my_class]['af_props'];
        }
        
        $schema = $this->getSchema();
        $types = array(
            Pfw_Db_Adapter::TYPE_DATETIME,
            Pfw_Db_Adapter::TYPE_TIME
        );
        $props = array();
        foreach ($schema as $field => $desc) {
            if (in_array($desc['type'], $types)) {
               $props[$field] = $desc['type'];
            }
        }
        
        self::$_description[$my_class]['af_props'] = $props;
        return $props;
    }


    /**
     * Sets the properties on this instance when fetched.
     * Fetching is assumed to come from a safe source such 
     * as a db.
     *
     * @param array $properties property => value pairs
     */
    public function formSetProperties($properties)
    {
        if (empty($properties)) { return; }
        $this->_setProperties($properties);
    }


    /**
     * This function should only be called internally as it 
     * bypasses any filtering. Assignment occurs only on properties
     * of this which are not already set
     *
     * @param array $properties property => value pairs
     */
    public function _setProperties($properties)
    {
        while (list($name, $value) = each($properties)) {
            // ignore it if it was already set
            if (is_valid_prop($name)) {
                $this->$name = $value;
            }
        }
    }


    /**
     * Does this instance have validation errors?
     *
     * @param string $field if null, check specific field, else
     * check all fields
     */
    public function hasErrors($field = null)
    {
        if (is_null($this->_errors)) {
            return false;
        }
        if (!is_null($field)) {
            return empty($this->_errors[$field]) ? false : true;
        }
        
        return empty($this->_errors) ? false : true;
    }


    /**
     * Add an error to a field with message
     *
     * @param string $field
     * @param string $message
     */
    public function addError($field, $message)
    {
        if (is_null($this->_errors)) {
            $this->errors = array();
        }
        if (false !== strpos($message, '%s')) {
            $message = sprintf($message, $this->$field);
        }
        $this->_errors[$field][] = $message;
    }


    /**
     * Get the validation errors for this instance
     *
     * @param string $field if null, get specific field errors, else
     * get errors on all fields
     */
    public function getErrors($field = null, $collapse = true)
    {
        if (is_null($this->_errors)) {
            return array();
        }
        if(!is_null($field)){
            return $this->_errors[$field];
        }
        if (true == $collapse) {
            $all_errors = array();
            foreach ($this->_errors as $field => $errors) {
                if(!is_array($errors)) {$errors = array($errors);}
                $all_errors = array_merge($all_errors, $errors);
            }
            return $all_errors;
        }
        return $this->_errors;
    }
    
    
    /**
    * Clear the validation errors for this instance
    *
    * @param string $field if null, clear specific field errors, else
    * clear errors on all fields
     */
    public function clearErrors($field = null)
    {
        if (!is_null($field)) {
            $this->_errors[$field] = array();
        }
        else {
            $this->_errors = array();
        }
    }


    /**
     * Sets the db adapter instance to use for this model instance.
     *
     * @param Pfw_Db_Adapter $db
     */
    public function _setDb($db)
    {
        $this->_db = $db;
    }


    /**
     * Gets the db adapter instance that this model is currently using.
     *
     * @param bool $reuse use an existing link
     * @return Pfw_Db_Adapter
     */
    public function _getDb($reuse = true)
    {
        if (true == $reuse) {
            if (!isset($this->_db)) {
                $this->_db = Pfw_Db::factory(null, $reuse);
            }
            return $this->_db;
        }
        return Pfw_Db::factory(null, $reuse);
    }


    /**
     * Gets the schema from the db.
     *
     * @return array
     */
    public function getSchema()
    {
        $my_class = $this->getClass();
        if (isset(self::$_description[$my_class]['schema'])) {
            return self::$_description[$my_class]['schema'];
        }

        $table = $this->getTable();
        $schema = $this->_getDb(true)->getTableSchema($table);
        self::$_description[$my_class]['schema'] = $schema;

        return $schema;
    }


    /**
     * Gets the names of the columns in the table which backs this model
     *
     * @return array
     */
    public function getDbFields()
    {
        return array_keys($this->getSchema());
    }


    /**
     * Sets the retrieved flag on this instance.
     * Used to determine if this object was fetched from the database.
     */
    public function _setRetrieved()
    {
        $this->_is_retrieved = true;
    }


    /**
     * Unsets the retrieved flag on this instance.
     */
    public function _unsetRetrieved()
    {
        $this->_is_retrieved = false;
    }


    /**
     * Is this model fetched from the database?
     *
     * @return bool
     */
    public function isRetrieved()
    {
        return $this->_is_retrieved;
    }


    /**
     * Gets the class name for this model.
     *
     * @return string
     */
    public function getClass()
    {
        $tmp_class = get_class($this);
        if (!isset(self::$_description[$tmp_class]['class'])) {
            self::$_description[$tmp_class]['class'] = $tmp_class;
        }
        return self::$_description[$tmp_class]['class'];
    }


    /**
     * Adds a set of fields to fetch statements.
     *
     * @param array $fields array of field => value pairs
     */
    public function setFetchFields($fields)
    {
        $my_class = $this->getClass();
        self::$_description[$my_class]['fetch_fields'] = $fields;
    }
    
    
    /**
     * Gets the set of fetch fields
     * @return array
     */
    public function getFetchFields()
    {
        $my_class = $this->getClass();
        return self::$_description[$my_class]['fetch_fields'];
    }


    /**
     * Adds a single field to fetch statements
     *
     * @param string $field the name of the field
     * @param mixed $value the value of field (generally a Zend_Db_Expr)
     */
    public function addFetchField($field, $value)
    {
        $my_class = $this->getClass();
        if (!isset(self::$_description[$my_class]['fetch_fields'])) {
            self::$_description[$my_class]['fetch_fields'] = array();
        }
        self::$_description[$my_class]['fetch_fields'][$field] = $value;
    }


    /**
     * Resets all fetch fields
     */
    public function resetFetchFields()
    {
        $my_class = $this->getClass();
        unset(self::$_description[$my_class]['fetch_fields']);
    }
    
    
    /**
     * Adds a set of block fields to fetch statements.
     *
     * @param array $fields array of field => value pairs
     */
    public function setBlockedFields($fields)
    {
        $my_class = $this->getClass();
        self::$_description[$my_class]['block_fetch_fields'] = array();
        foreach ($fields as $field) {
            self::$_description[$my_class]['block_fetch_fields'][$field] = 1;
        }
    }
    
    
    
    /**
     * Adds a set of block fields to fetch statements.
     *
     * @param array $fields array of field => value pairs
     */
    public function addBlockedField($field)
    {
        $my_class = $this->getClass();
        if (!isset(self::$_description[$my_class]['block_fetch_fields'])) {
            self::$_description[$my_class]['block_fetch_fields'] = array();
        }
        self::$_description[$my_class]['block_fetch_fields'][$field] = 1;
    }
    
    
    /**
     * Gets the set of blocked fields 
     *
     * @return array
     */
    public function getBlockedFields(){
        $my_class = $this->getClass();
        self::$_description[$my_class]['block_fetch_fields'];
    }
    
    
    /**
     * Resets all fetch fields
     */
    public function resetBlockedFields()
    {
        $my_class = $this->getClass();
        unset(self::$_description[$my_class]['block_fetch_fields']);
    }


    /**
     * Sets the table name that backs this model. Should be called from
     * within the setup() method.
     *
     * @param string $name
     */
    protected function setTable($name)
    {
        $my_class = $this->getClass();
        self::$_description[$my_class]['table'] = $name;
    }


    /**
     * Gets the table name that backs this model.
     *
     * @return string
     */
    public function getTable()
    {
        $my_class = $this->getClass();
        if (isset(self::$_description[$my_class]['table'])) {
            return self::$_description[$my_class]['table'];
        }

        $table = pluralize(strtolower($my_class));
        self::$_description[$my_class]['table'] = $table;

        return $table;
    }


    /**
     * Sets the primary key name used by this model. Should be called from
     * within the setup() method.
     *
     * @param string $field
     */
    protected function setPrimaryKey($field)
    {
        $my_class = $this->getClass();
        self::$_description[$my_class]['primary_key'] = $field;
    }


    /**
     * Gets the name of the primary key for this model.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        $my_class = $this->getClass();
        if (!isset(self::$_description[$my_class]['primary_key'])) {
            self::$_description[$my_class]['primary_key'] = 'id';
        }
        return self::$_description[$my_class]['primary_key'];
    }


    /**
     * Defines a belongs to relationship. Should be called from
     * within the setupAssociations method.
     *
     * @param string $as the name of the association
     * @param $params the association parameters
     */
    protected function belongsTo($as, $params = array())
    {
        $my_class = $this->getClass();
        if (isset(self::$_description[$my_class]['has'][$as])) {
            return;
        }

        $params['count'] = self::ASSOC_BELONGSTO;

        // guarantee we have necessary params
        if (!isset($params['class'])) {
            $params['class'] = ucfirst($as);;
        }

        if (!isset($params['foreign_key'])) {
            $params['foreign_key'] = strtolower($as)."_id";
        }

        if (!isset($params['owner_key'])) {
            $params['owner_key'] = 'id';
        }

        if (!isset($params['table'])) {
            $params['table'] = pluralize($as);
        }

        if (!isset($params['default_strategy'])) {
            $params['default_strategy'] = 'Immediate';
        }

        self::$_description[$my_class]['has'][$as] = $params;
    }


    /**
     * Defines a has many relationship. Should be called from
     * within the setupAssociations method.
     *
     * @param string $as the name of the association
     * @param $params the association parameters
     */
    public function hasMany($as, $params = array())
    {
        $my_class = $this->getClass();
        if (isset(self::$_description[$my_class]['has'][$as])) {
            return;
        }

        $params['count'] = self::ASSOC_MANY;

        // guarantee we have necessary params
        if (!isset($params['class'])) {
            $params['class'] = ucfirst(singularize($as));
        }
        if (!isset($params['my_key'])) {
            $params['my_key'] = $this->getPrimaryKey();
        }
        if (!isset($params['table'])) {
            $params['table'] = $as;
        }

        if (isset($params['thru'])) {
            $this->_setupThruParams($params);
        } else {
            if (!isset($params['foreign_key'])) {
                $params['foreign_key'] = strtolower($my_class)."_id";
            }
        }

        if (!isset($params['default_strategy'])) {
            $params['default_strategy'] = 'Pfw_Associate_PostQuery';
        }

        self::$_description[$my_class]['has'][$as] = $params;
    }


    /**
     * Defines a has one relationship. Should be called from
     * within the setupAssociations method.
     *
     * @param string $as the name of the association
     * @param $params the association parameters
     */
    public function hasOne($as, $params = array())
    {
        $my_class = $this->getClass();
        if (isset(self::$_description[$my_class]['has'][$as])) {
            return;
        }

        $params['count'] = self::ASSOC_ONE;

        /**
         * guarantee we have necessary params
         */
        if (!isset($params['class'])) {
            $params['class'] = ucfirst($as);;
        }
        if (!isset($params['my_key'])) {
            $params['my_key'] = $this->getPrimaryKey();
        }
        if (!isset($params['table'])) {
            $params['table'] = pluralize($as);
        }

        if (isset($params['thru'])) {
            $this->_setupThruParams($params);
        }
        else {
            if (!isset($params['foreign_key'])) {
                $params['foreign_key'] = strtolower($my_class)."_id";
            }
        }

        if (!isset($params['default_strategy'])) {
            $params['default_strategy'] = 'Immediate';
        }

        self::$_description[$my_class]['has'][$as] = $params;
    }


    /**
     * Helper method which sets up paramaters for thru relationships.
     *
     * @param array $params
     */
    private function _setupThruParams(&$params) {
        // the foreign key in the middle table for the table on the left
        if (!isset($params['my_join_key'])) {
            $prefix = singularize($this->getTable());
            $params['my_join_key'] = "{$prefix}_{$params['my_key']}";
        }
        // the key in the table on the right
        if (!isset($params['as_key'])) {
            // if its our class, we have more insight because we easily get the key name
            if ($params['class'] == $this->getClass()) {
                $params['as_key'] = $this->getPrimaryKey();
            }
            // we could instantiate the class, and call its getPrimaryKey, but that would
            // be a lot less effecient
            else {
                $params['as_key'] = 'id';
            }
        }
        // the foreign key in the middle table for the table on the right
        if (!isset($params['as_join_key'])) {
            $prefix = singularize($params['table']);
            $params['as_join_key'] = "{$prefix}_{$params['as_key']}";
        }
        // the class of the thru relationship
        if (!isset($params['thru_class'])) {
            $params['thru_class'] = 'Pfw_Model_Thru';
        }
        // the table in the middle
        if (!isset($params['thru_table'])) {
            $params['thru_table'] = pluralize($params['thru']);
        }
        // setup the fields in the thru table
        if (!isset($params['thru_fields'])) {
            $params['thru_fields'] = array($params['my_join_key'], $params['as_join_key']);
        }
    }


    /**
     * Associate this model instance with another model instance.
     *
     * @param Pfw_Model $object the object to associate
     * @param string $as the association name we want to add $object as. this
     * argument is required if multiple associations are of the same class as $object
     */
    public function associateWith($object, $as = null)
    {
        $this->_setupAssociations();

        $with_class = $object->getClass();
        $my_class = $this->getClass();

        if (!isset(self::$_description[$my_class]['has'])) {
            self::$_description[$my_class]['has'] = array();
        }

        // if 'as' is not passed in, try to determine the relationship we want
        if (empty($as)) {
            $associations = self::$_description[$my_class]['has'];
            foreach ($associations as $a => $params) {
                if ($params['class'] == $with_class) {
                    if ($as !== null) {
                        //trigger_error("Warning, $as is a potentiall ambiguous association", E_USER_NOTICE);
                        /*
                        Pfw_Loader::loadClass('Pfw_Exception_Model');
                        throw new Pfw_Exception_Model(
                        "Association of '$with_class' is ambiguious - multiple associations of this class. ".
                        "You must use 'as' paramater to disambiguate."
                        );
                        */
                    }
                    $as = $a;
                }
            }
        }

        if (!isset(self::$_description[$my_class]['has'][$as])) {
            if (empty($as)) { $as = $object->getClass(); }
            throw new Pfw_Exception_Model("Class '$my_class' does not define an association for '$as'.");
        }

        $with_params =& self::$_description[$my_class]['has'][$as];

        if (isset($with_params['on_associate'])) {
            if (!method_exists($this, $with_params['on_associate'])) {
                throw new Pfw_Exception_Model(
                  "Class '$my_class' does not define an association method named '{$with_params['on_associate']}'"
                );
            }
            $method = $with_params['on_associate'];
            $with_params['on_associate'] = null;
            $ret = $this->$method($object, $as);
            $with_params['on_associate'] = $method;
            return $ret;
        }

        $num = $with_params['count'];
        if (($num == self::ASSOC_ONE) or ($num == self::ASSOC_MANY)) {
            if (isset($with_params['thru'])) {
                $thru_class = $with_params['thru_class'];
                Pfw_Loader::loadClass($thru_class);
                $thru = new $thru_class($this->getClass(), $as, $with_params);
                $thru->associate($this, $object);
            } else {
                $my_key = $with_params['my_key'];
                $my_id = $this->$my_key;
                if (empty($my_id)) {
                    throw new Pfw_Exception_Model(
                        "Can't make an association to an object with empty '$my_key' field (save {$my_class} first?)"
                    );
                }
                $fk = $with_params['foreign_key'];
                $object->$fk = $my_id;
                $object->save();
            }
        }
        elseif ($num == self::ASSOC_BELONGSTO) {
            $owner_key = $with_params['owner_key'];
            $owner_key_value = $object->$owner_key;
            if (empty($owner_key_value)) {
                $owner_class = get_class($object);
                throw new Pfw_Exception_Model(
                    "Can't make an association to an object with empty '$owner_key' field (save {$owner_class} first?)"
                );
            }
            $fk = $with_params['foreign_key'];
            $this->$fk = $owner_key_value;
        }
    }


    /**
     * Resets the given association description, if its defined.
     *
     * @param string $as
     */
    public function resetAssociationDescription($as)
    {
        $this->_setupAssociations();

        $my_class = $this->getClass();
        unset(self::$_description[$my_class]['has'][$as]);
    }


    /**
     * Does this model define the given association?
     *
     * @param $as the name of the association
     */
    public function _definesAssociation($as)
    {
        $this->_setupAssociations();

        $my_class = $this->getClass();
        if (!isset(self::$_description[$my_class]['has'])) {
            return false;
        }

        $all_assoc = array_keys(self::$_description[$my_class]['has']);
        return in_array($as, $all_assoc);
    }


    /**
     * Get the description for the given association.
     *
     * @param $as the name of the association
     * @return array
     */
    public function _getAssociationDescription($as)
    {
        $this->_setupAssociations();

        $my_class = $this->getClass($this);
        if (!$this->_definesAssociation($as)) {
            return false;
        }

        return self::$_description[$my_class]['has'][$as];
    }


    /**
     * Helper method wrapper for setupAssociations.
     */
    protected function _setupAssociations() {
        if ((false === $this->_have_run_assoc_setup) and method_exists($this, 'setupAssociations')) {
            $this->setupAssociations();
            $this->_have_run_assoc_setup = true;
        }
    }


    /**
     * Limits the field that are used on insert.
     * This method should be called from within the setup() method.
     *
     * @param array $fields flat array of fields that inserts are limited to
     */
    protected function setInsertFields($fields)
    {
        $my_class = $this->getClass();
        self::$_description[$my_class]['insert_fields'] = $fields;
    }


    /**
     * Gets the fields that inserts are limited to.
     *
     * @return array flat array of fields that inserts are limited to
     */
    public function getInsertFields()
    {
        $my_class = $this->getClass();
        if (!isset(self::$_description[$my_class]['insert_fields'])) {
            self::$_description[$my_class]['insert_fields'] = null;
        }
        return self::$_description[$my_class]['insert_fields'];
    }


    /**
     * Limits the field that are used on update.
     * This method should be called from within the setup() method.
     *
     * @param array $fields flat array of fields to insert
     */
    protected function setUpdateFields($fields)
    {
        $my_class = $this->getClass();
        self::$_description[$my_class]['update_fields'] = $fields;
    }


    /**
     * Gets the fields that updates are limited to.
     *
     * @return array flat array of fields that updates are limited to
     */
    public function getUpdateFields()
    {
        $my_class = $this->getClass();
        if (!isset(self::$_description[$my_class]['update_fields'])) {
            self::$_description[$my_class]['update_fields'] = null;
        }
        return self::$_description[$my_class]['update_fields'];
    }


    /**
     * Gets the full description of this model.
     *
     * @return array the model description
     */
    public function &_getDescription()
    {
        // ensure the description is populated
        $class = $this->getClass();
        $this->getTable();
        $this->getPrimaryKey();
        $this->getUpdateFields();
        $this->getInsertFields();
        $this->getSchema();
        $this->_setupAssociations();

        return self::$_description[$class];
    }
}
