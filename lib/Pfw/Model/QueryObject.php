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

Pfw_Loader::loadClass("Pfw_Model_Thru");

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Model_QueryObject
{
    protected $description;
    /**
     * @var Pfw_Db_Adapter
     */
    protected $adapter;
    protected $inst;
    /**
     * @var Pfw_Db_Select
     */
    protected $select;
    // Immediate associations
    protected $with;
    // Strategy associations
    protected $withStrategy;
    // Descriptions of associated models
    protected $withDesc;

    // Thru related stuff
    protected $thruDesc;
    protected $hasThru = false;
    protected $first = false;

    public function __construct($class, $db = null)
    {
        $this->with = array();
        $this->withStrategy = array();
        $this->withDesc = array();
        $this->thruDesc = array();

        if (is_object($class)) {
            $this->inst = $class;
        } else {
            $this->inst = new $class();
        }

        if (isset($db)) {
            $this->adapter = $db;
        } else {
            $this->adapter = $this->inst->_getDb();
        }

        if (null !== $db) {
            $this->inst->_setDb($db);
        }

        $this->select = $this->adapter->getSelectBuilder();
        $this->description = $this->inst->_getDescription();

        $this->init();
    }

    public function init()
    {
        // set select from clause here
        $table  = $this->description['table'];
        $schema = $this->description['schema'];
        $fields = array_to_hash(array_keys($schema));
        
        if (isset($this->description['fetch_fields'])) {
            $fields = array_to_hash($this->description['fetch_fields']);
        }
                
        // remove anything that should be blocked
        if (isset($this->description['block_fetch_fields'])) {
            $block_fetch_fields = array_keys($this->description['block_fetch_fields']);
            foreach($fields as $alias => $field){
                if(in_array($field, $block_fetch_fields)){
                    unset($fields[$alias]);
                }
            }
        }
        
        $select_fields = array();
        foreach ($fields as $alias => $field) {
            // in the standard case $alias and $field with have same value,
            // when extra fetch fields are added, they will be different
            $select_fields["this.{$alias}"] = $field;
            if (is_a($field, 'Pfw_Db_Expr')) {
                $field->setAlias('this');
            }
        }
        
        $this->select->from(array('this' => $table), $select_fields);
        if (method_exists($this, 'beforeFetchFirst')) {
            $this->beforeFetchFirst();
        }
    }
    
    public function insertAll($dataset) {
        $table = $this->description['table'];
        return $this->adapter->insertAllIntoTable($table, $dataset);
    }
    
    /**
     * Updates a set of records in the table that backs this model
     * 
     * @param array $data field => new_value pairs
     * @param array $where sprintf style array
     * @return integer the number of rows affected
     */
    public function updateAll($data, $where) {
        $table = $this->description['table'];
        return $this->adapter->updateTable(
            $table, $data, $where
        );
    }
    
    /**
     * Deletes a set of records in the table that backs this model
     * 
     * @param array $where sprintf style array 
     * @return integer the number of rows affected
     */
    public function deleteAll($where) {
        $table = $this->description['table'];
        return $this->adapter->deleteFromTable(
            $table, $where
        );
    }


    /**
     * Fetches the record with the given primary key value
     * 
     * @param mixed $primary_key_value
     * @return Pfw_Model
     */
    public function getById($primary_key_value)
    {
        $desc = $this->description;
        return $this->where(array("this.{$desc['primary_key']} = %s", $primary_key_value))->first();
    }


    /**
     * end base query functions
     */
    
    /**
     * Gets the Pfw_Db_Select object used for this QueryObject
     * 
     * @return Pfw_Db_Select
     */
    public function _getSelect()
    {
        return $this->select;
    }


    /**
     * Adds a WHERE clause onto this query object
     * 
     * @param array $where sprintf style array
     * @return Pfw_Model_QueryObject
     */
    public function where($where, $alias = null)
    {
        $this->select->where($where, $alias);
        return $this;
    }
    
    
    /**
     * Adds a WHERE ... IN clause onto this query object
     * 
     * @param string $field the field whose values must be in $val_arr
     * @param array $val_arr the list of possible values for field
     * @return Pfw_Model_QueryObject
     */
    public function whereIn($field, $val_arr, $alias = null)
    {
        $this->select->whereIn($field, $val_arr, $alias = null);
        return $this;
    }


    /**
     * Adds an ORDER BY clause onto this query object
     * 
     * @param string $order ex: "myfield DESC"
     * @return Pfw_Model_QueryObject
     */
    function orderBy($order)
    {
        $this->select->order($order);
        return $this;
    }
    
    
    /**
     * Adds an GROUP BY clause onto this query object
     * 
     * @param string $group 
     * @return Pfw_Model_QueryObject
     */
    function groupBy($group)
    {
    	$this->select->group($group);
    	return $this;
    }
    
    
    /**
     * Adds a HAVING clause onto this query object
     * 
     * @param string $having 
     * @return Pfw_Model_QueryObject
     */
    function having($having)
    {
    	$this->select->having($having);
    	return $this;
    }


    public function limit($limit)
    {
        $this->select->limit($limit);
        return $this;
    }
    
    public function first()
    {
        $this->first = true;
        return $this;	
    }

    public function offset($offset)
    {
        $this->select->offset($offset);
        return $this;
    }


    public function with($model, $options = array())
    {
        if (empty($model)) {
            return $this;
        }

        if (!isset($this->description['has'][$model])) {
            Pfw_Loader::loadClass('Pfw_Exception_Model');
            $my_class = $this->description['class'];
            throw new Pfw_Exception_Model(
                "Class '$my_class' does not define an association for '$model'"
            );
        }

        $assoc = $this->description['has'][$model];

        // determine the associate strategy
        if (isset($options['join_strategy'])) {
            if ($options['join_strategy'] == "Immediate") {
                $this->with[$model] = $options;
            } else{
                $this->withStrategy[$model] = $options;
            }
        } elseif(isset($assoc['default_strategy'])) {
            if ($assoc['default_strategy'] == "Immediate") {
                $options['join_strategy'] = "Immediate";
                $this->with[$model] = $options;
            } else {
                $options['join_strategy'] = $assoc['default_strategy'];
                $this->withStrategy[$model] = $options;
            }
        } else {
            $options['join_strategy'] = "Immediate";
            $this->with[$model] = $options;
        }

        return $this;
    }

    protected function confirmRels($models)
    {
        $invalid = false;
        if (!isset($this->description['has'])) {
            $invalid = true;
            $inv_model = $models[0];
        } else{
            $joins_on = array_keys($this->description['has']);
            foreach($models as $m) {
                if (!in_array($m, $joins_on)) {
                    $invalid = true;
                    $inv_model = $m;
                    break;
                }
            }
        }

        if ($invalid) {
            Pfw_Loader::loadClass('Pfw_Exception_Model');
            throw new Pfw_Exception_Model(
                "'$inv_model' is not a relationship defined by {$this->description['class']}"
            );
        }
    }


    public function exec($options = array(), $output_sql = false)
    {
        $this->doJoins();

        if (true == $output_sql) {
            return $this->select->__toString();
        }

        $rs = $this->select->exec();
        $ret = $this->_mapFields($rs, $options);
        $this->doJoinStrategies($ret);

        if (($this->first) or (1 == $this->select->getLimit())) {
            if(empty($ret)) {
                return null;
            }
            return $ret[0];
        }

        return $ret;
    }


    protected function doThruJoin($with)
    {
        $class = $this->description['has'][$with]['class'];
        $fk = $this->description['has'][$with]['foreign_key'];
        $count = $this->description['has'][$with]['count'];
    }


    protected function doJoins()
    {
        $withs = array_keys($this->with);
        if (empty($withs)) {
            return;
        }

        foreach ($withs as $with) {
            $with_params = $this->description['has'][$with];

            // get the model description for the association model
            $with_model_class = $with_params['class'];
            Pfw_Loader::loadModel($with_model_class);
            $model_inst = new $with_model_class();
            $with_model_desc = $model_inst->_getDescription();
            $this->withDesc[$with] = $with_model_desc;


            $with_model_fields = array_to_hash(array_keys($with_model_desc['schema']));

            // remove anything that should be blocked
            if (isset($with_model_desc['block_fetch_fields'])) {
                $block_fetch_fields = array_keys($with_model_desc['block_fetch_fields']);
                foreach($with_model_fields as $alias => $field){
                    if(in_array($field, $block_fetch_fields)){
                        unset($with_model_fields[$alias]);
                    }
                }
            }

            if (isset($with_model_desc['fetch_fields'])) {
                $with_model_fields = array_merge(
                    $with_model_fields, $with_model_desc['fetch_fields']
                );
            }

            // all of the fields in the association model
            $with_select_fields = array();
            foreach ($with_model_fields as $field => $value) {
                if (is_a($value, 'Pfw_Db_Expr')) {
                    $value->setAlias($with);
                }
                $with_select_fields["{$with}.{$field}"] = $value;
            }
            
            // just an alias
            $join_extra_cond = "";
            if (isset($with_params['conditions'])) {
                $join_extra_cond .= " AND {$with_params['conditions']}";
            }

            $ct = $with_params['count'];
            if ($ct == Pfw_Model::ASSOC_BELONGSTO) {
                $cond = "this.{$with_params['foreign_key']} = {$with}.{$with_params['owner_key']}";
                $this->select->joinLeft(
                    array($with => $with_params['table']),
                    array("{$cond}{$join_extra_cond}"),
                    $with_select_fields
                );
            } elseif (($ct == Pfw_Model::ASSOC_ONE) or ($ct == Pfw_Model::ASSOC_MANY)) {
                $my_key = $this->description['has'][$with]['my_key'];

                if (isset($with_params['thru'])){
                    $this->hasThru = true;
                    $this->thruDesc[$with_params['thru']] = array(
                        'owned_by' => $with, 'class' => $with_params['thru_class']
                    );

                    // setup the select fields for the thru table
                    $thru_fields = array_to_hash($with_params['thru_fields']);
                    $thru_select_fields = array();
                    foreach ($thru_fields as $field) {
                        $thru_select_fields["{$with_params['thru']}.{$field}"] = $field;
                    }

                    // us->join table
                    $thru_extra_cond = "";
                    if (isset($with_params['thru_conditions'])) {
                        $thru_extra_cond = "AND {$with_params['conditions']}";
                    }
                    $cond = "this.{$with_params['my_key']} = {$with_params['thru']}.{$with_params['my_join_key']}";
                    $this->select->joinLeft(
                        array($with_params['thru'] => $with_params['thru_table']),
                        array("{$cond}{$thru_extra_cond}"),
                        $thru_select_fields
                    );
                    // join table->association
                    $cond = "{$with_params['thru']}.{$with_params['as_join_key']} = {$with}.{$with_params['as_key']}";
                    $this->select->joinLeft(
                        array($with => $with_params['table']),
                        array("{$cond}{$join_extra_cond}"),
                        $with_select_fields
                    );
                } else {
                    // us->association
                    $cond = "this.{$with_params['my_key']} = {$with}.{$with_params['foreign_key']}";
                    $this->select->joinLeft(
                        array($with => $with_params['table']),
                        array("{$cond}{$join_extra_cond}"),
                        $with_select_fields
                    );
                }
            }
        }
    }


    public function &_mapFields(&$rs, $options)
    {
        $tmp = array();
        $obj_registry = array();
        $this_pk_name = $this->description['primary_key'];
        $my_class = $this->description['class'];

        $len = count($rs);
        for($i = 0; $i < $len; $i++) {
            $d = $rs[$i];
            $ns_registry = array();
            // get all of the object->property mappings of a single row
            foreach ($d as $prop => $value) {
                // parse the namespace from the property name
                $period_pos = strpos($prop, '.');
                if ($period_pos == 0) {
                    $ns = 'this';
                } else {
                    $ns = substr($prop, 0, $period_pos);
                    $prop = substr($prop, $period_pos + 1);
                }
                $ns_registry[$ns][$prop] = $value;
            }

            // we're still within one row
            $this_pk_value = $ns_registry['this'][$this_pk_name];
            foreach ($ns_registry as $ns => $ns_props) {
                // property is part of parent
                if (($ns == 'this')) { // the property is a member if 'this'
                    if (!isset($obj_registry[$this_pk_value])) {
                        $obj_registry[$this_pk_value] = $ns_props;
                    }
                }
                // the property is a member object from an association
                else {
                    if ($this->hasThru and isset($this->thruDesc[$ns])) {
                        $owner_ns = $this->thruDesc[$ns]['owned_by'];
                        $owner_ns_pk_name = $this->withDesc[$owner_ns]['primary_key'];
                        $owner_ns_pk_value = $ns_registry[$owner_ns][$owner_ns_pk_name];

                        $class = $this->thruDesc[$ns]['class'];
                        $inst = new $class($my_class, $ns);
                        $inst->setProperties($ns_props);
                        $obj_registry[$this_pk_value][$owner_ns][$owner_ns_pk_value][$ns] = $inst;
                    } elseif (isset($this->withDesc[$ns])) {
                        // its a defined association
                        $ns_pk_name = $this->withDesc[$ns]['primary_key'];
                        $ns_pk_value = $ns_registry[$ns][$ns_pk_name];
                        if (!empty($ns_pk_value)) {
                            if (isset($obj_registry[$this_pk_value][$ns][$ns_pk_value])){
                                $obj_registry[$this_pk_value][$ns][$ns_pk_value] = array_merge(
                                    $ns_props,
                                    $obj_registry[$this_pk_value][$ns][$ns_pk_value]
                                );
                            } else {
                                $obj_registry[$this_pk_value][$ns][$ns_pk_value] = $ns_props;
                            }
                        } else {
                            $obj_registry[$this_pk_value][$ns] = null;
                        }
                    }
                    // its not a defined association, just becomes a member of the parent
                    // as an assoc array
                    else {
                        $obj_registry[$this_pk_value][$ns] = $ns_props;
                    }
                }

            }
            unset($rs[$i]);
        }

        #print_r($obj_registry);

        $ret = array();
        $as_array = false;
        if (isset($options['as_array']) and ($options['as_array'] == true)) {
            $as_array = true;
        }

        $with = array_keys($this->with);

        foreach ($obj_registry as $pk => $props) {
            $my_inst = new $my_class();

            foreach ($props as $prop => $value) {
                // if the prop is one of our associations
                if (in_array($prop, $with)) {
                    $numericity = $this->description['has'][$prop]['count'];
                    $prop_class = $this->description['has'][$prop]['class'];
                    if ((Pfw_Model::ASSOC_ONE == $numericity) or (Pfw_Model::ASSOC_BELONGSTO == $numericity)) {
                        if (null === $value) {
                            $my_inst->$prop = null;
                        } else {
                            $the_props_arr = array_values($obj_registry[$pk][$prop]);
                            if (false == $options['as_array']) {
                                $prop_inst = new $prop_class();
                                $prop_inst->fetchSetProperties($the_props_arr[0]);
                                $prop_inst->_setRetrieved();
                                $my_inst->$prop = $prop_inst;
                            } else {
                                $my_inst->$prop = $the_props_arr[0];
                             }
                        }
                    } elseif (Pfw_Model::ASSOC_MANY == $numericity) {
                        if (null === $value) {
                            $my_inst->$prop = array();
                        } else {
                            $the_props_arr = array_values($obj_registry[$pk][$prop]);
                            $my_inst->$prop = array();
                            foreach ($the_props_arr as $the_props) {
                                if (false == $options['as_array']) {
                                    $prop_inst = new $prop_class();
                                    $prop_inst->fetchSetProperties($the_props);
                                    $prop_inst->_setRetrieved();
                                    array_push($my_inst->$prop, $prop_inst);
                                } else {
                                    array_push($my_inst->$prop, $the_props);
                                }

                            }
                        }
                    }
                    unset($props[$prop]);
                }
            }

            $my_inst->fetchSetProperties($props);
            $my_inst->_setRetrieved();
            
            // use the same db connection as QO which fetched me
            // kinda weird to do this here, but save us from iterating again
            $my_inst->_setDb($this->adapter);
            
            array_push($ret, $my_inst);
        }

        return $ret;
    }


    protected function doJoinStrategies(&$ret)
    {
        if (empty($this->withStrategy)) { return; }

        foreach ($this->withStrategy as $assoc => $options) {
            $strategy_class = $options['join_strategy'];
            unset($options['join_strategy']);
            Pfw_Loader::loadClass($strategy_class);
            $strategy = new $strategy_class();
            $strategy->exec($ret, $assoc, $options);
            $strategy = null; $strategy_class = null;
        }
    }
}
