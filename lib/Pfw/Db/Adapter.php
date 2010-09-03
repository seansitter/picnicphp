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
abstract class Pfw_Db_Adapter
{
    protected $query_history = array();
    protected $is_shared = false;
    protected $link;
    protected $route;

    abstract public function connect();
    abstract public function disconnect();
    abstract public function fetchOne($sql);
    abstract public function fetchAll($sql);
    abstract public function update($sql);
    abstract public function delete($sql);
    abstract public function insert($sql);
    abstract public function insertAll($sql);
    abstract public function insertOrUpdateTable(
        $table, $unique_name, $unique_value, $data_insert, $data_update = null
    );
    abstract public function query($sql);
    abstract public function getSelectBuilder();
    abstract public function getTableSchema($table);
    abstract public function esc($str);
    abstract public function quoteIdentifier($identifier);
    abstract public function getLinkId();
    abstract public function getRoute();
    abstract public function beginTxn();
    abstract public function commitTxn();
    abstract public function rollbackTxn();
    abstract public function setAutoCommit($autocommit);

    const TYPE_VARCHAR   = 0;
    const TYPE_CHARACTER = 1;
    const TYPE_TEXT      = 2;
    const TYPE_BINARY    = 3;
    const TYPE_INTEGER   = 4;
    const TYPE_FLOAT     = 5;
    const TYPE_DATETIME  = 6;
    const TYPE_TIME      = 7;
    const TYPE_ENUM      = 8;

    const SIGN_SIGNED    = 0;
    const SIGN_UNSIGNED  = 1;

    public function __construct($route)
    {
        $this->route = $route;
    }

    public function getLink()
    {
        $this->connect();
        return $this->link;
    }

    public function isSharedCnx($is_shared = null)
    {
        if (!is_null($is_shared)) {
            $this->is_shared = $is_shared;
            return $is_shared;
        }
        return $this->is_shared;
    }

    protected function addHistory($sql)
    {
        array_push($this->query_history, $sql);
    }

    public function getHistory()
    {
        return $this->query_history;
    }

    public function updateTable($table, $data, $where)
    {
        $stmt = "";
        $update_data = $this->formatUpdateData($data);

        $table = $this->quoteIdentifier($table);
        $stmt = "UPDATE {$table} SET {$update_data}";
        if (null !== $where) {
            $where = $this->_sprintfEsc($where);
            $stmt = "{$stmt} WHERE {$where}";
        }

        return $this->update($stmt);
    }
    
    public function formatUpdateData($data)
    {
        $formatted_data = "";
        foreach ($data as $field => $value) {
            $field = $this->quoteIdentifier($field);
            if(null === $value){
                $formatted_data .= "{$field} = NULL, ";
            } elseif(is_a($value, 'Pfw_Db_Expr')) {
                $formatted_data .= "{$field} = {$value->getValue()}, ";
            } else {
                $value = $this->esc($value);
                $formatted_data .= "{$field} = '{$value}', ";
            }
        }
        
        return rtrim($formatted_data, ', ');
    }
    
    public function insertAllIntoTable($table, $dataset) 
    {
        list ($fields_part, $data_part) = $this->formatMultiInsertData($dataset, $table);
        
        $table = $this->quoteIdentifier($table);
        $stmt = "INSERT INTO {$table} {$fields_part} VALUES {$data_part}";
        
        return $this->insertAll($stmt);
    }

    public function insertIntoTable($table, $data)
    {
        list($fields_part, $data_part) = $this->formatInsertData($data, $table);
        
        $table = $this->quoteIdentifier($table);
        $stmt = "INSERT INTO {$table} {$fields_part} VALUES {$data_part}";
        
        return $this->insert($stmt);
    }
    
    public function formatInsertData($data, $table = null)
    {
        $fields = array();
        $fields_part = "";
        $data_part = "";

        $table_cols = null;
        if (!is_null($table)) {
            $table_cols = array_keys($this->getTableSchema($table));
        }

        foreach ($data as $field => $value) {
            if (!is_null($table_cols) and !in_array($field, $table_cols)) {
                continue;
            }
            $field = $this->quoteIdentifier($field);
            array_push($fields, $field);
            
            if (is_array($value)) {
                $data_part .= "{$this->_sprintfEsc($value)}, ";
            } elseif(null === $value) {
                $data_part .= "NULL, ";
            } elseif(is_a($value, 'Pfw_Db_Expr')) {
                $data_part .= "{$value->getValue()}, ";
            } else {
                $data_part .= "'{$this->esc($value)}', ";
            }
        }
        
        $fields_part = implode(', ', $fields);
        $fields_part = '('.rtrim($fields_part, ', ').')';
        $data_part = '('.rtrim($data_part, ', ').')';
        
        return array($fields_part, $data_part);
    }
    
    public function formatMultiInsertData($dataset, $table = null)
    {
        $data_part_arr = array();
        $fields = array();
        $table_cols = null;
        if (!is_null($table)) {
            $table_cols = array_keys($this->getTableSchema($table));
        }
        
        # first pass, collect fields
        foreach ($dataset as $data) {
            foreach ($data as $field => $value) {
                # TODO in_array not efficient here
                if (!empty($field) and !in_array($field, $fields)) {
                    if (!is_null($table_cols) and !in_array($field, $table_cols)) {
                        continue;
                    }
                    array_push($fields, $field);
                }
            }
        }
        
        # second pass, create parts
        foreach ($dataset as $data) {
            $data_part = "";
            foreach ($fields as $field) {
                if (!isset($data[$field])) {
                    $data_part .= "NULL, ";
                } elseif(is_array($data[$field])) {
                     $data_part .= "{$this->_sprintfEsc($data[$field])}, ";
                } elseif(is_a($data[$field], 'Pfw_Db_Expr')) {
                    $expr = $data[$field];
                    $data_part .= "{$expr->getValue()}, ";
                } else {
                    $data_part .= "'{$this->esc($data[$field])}', ";
                }
            }
            $data_part = rtrim($data_part, ', ');
            $data_part = "({$data_part})";
            array_push($data_part_arr, $data_part);
        }
        
        # implode the data part array
        $fields_part = '('.implode(', ', $fields).')';
        $data_part = implode(', ', $data_part_arr);
        
        return array($fields_part, $data_part);
    }

    public function deleteFromTable($table, $where)
    {
        $table = $this->quoteIdentifier($table);
        $stmt = "DELETE FROM {$table}";
        if (!empty($where)) {
            if (is_array($where)) {
                $where = $this->_sprintfEsc($where);
            }
            $stmt = "{$stmt} WHERE {$where}";
        }

        return $this->update($stmt);
    }

    public function _sprintfEsc($fmt_arr, $inc_quotes = true)
    {
        $fmt_str = array_shift($fmt_arr);
        if (empty($fmt_arr)) {
            return $fmt_str;
        }

        $len = count($fmt_arr);
        for ($i = 0; $i < $len; $i++) {
            $fmt_arr[$i] = $this->esc($fmt_arr[$i]);
            if ($inc_quotes) {
              $fmt_arr[$i] = '\''.$fmt_arr[$i].'\'';
            }
        }
        
        array_unshift($fmt_arr, $fmt_str);
        return call_user_func_array('sprintf', $fmt_arr);
    }
}
