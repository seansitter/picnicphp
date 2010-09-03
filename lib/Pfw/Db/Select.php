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
abstract class Pfw_Db_Select
{
    const PART_FROM     = 1;
    const PART_WHERE    = 2;
    const PART_LIMIT    = 3;
    const PART_OFFSET   = 4;
    const PART_GROUPBY  = 5;
    const PART_HAVING   = 6;

    const JOIN_CROSS    = 1;
    const JOIN_INNER    = 2;
    const JOIN_LEFT     = 3;
    const JOIN_RIGHT    = 4;

    /**
     * @var Pfw_Db_Adapter
     */
    protected $adapter = null;

    protected $from       = array();
    protected $where      = array();
    protected $joins      = array();
    protected $limit      = null;
    protected $offset     = null;
    protected $having     = null;
    protected $groupby    = null;
    protected $orderby    = null;
    protected $paginator  = null;
    protected $join_types = array();
    protected $options    = null;
    protected $pq_class   = null;
    protected $pq_method  = null;

    public function __construct($adapter)
    {
        $this->join_types[] = self::JOIN_CROSS;
        $this->join_types[] = self::JOIN_INNER;
        $this->join_types[] = self::JOIN_LEFT;
        $this->join_types[] = self::JOIN_RIGHT;

        $this->join_str[self::JOIN_CROSS] = "CROSS JOIN";
        $this->join_str[self::JOIN_INNER] = "INNER JOIN";
        $this->join_str[self::JOIN_LEFT] = "LEFT JOIN";
        $this->join_str[self::JOIN_RIGHT] = "RIGHT JOIN";

        if (!is_a($adapter, 'Pfw_Db_Adapter')) {
            throw new Pfw_Exception_Db(
                 "Adapter is not an instance of 'Pfw_Db_Adapter"
            );
        }
        $this->adapter = $adapter;
    }
    
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;
    }
 
    public function getPaginator()
    {
        return $this->paginator;
    }
    

    public function resetPart($part)
    {
        switch ($part) {
            case self::PART_FROM:
                $this->from = array();
                break;
            case self::PART_WHERE:
                $this->where = array();
                break;
            case self::PART_LIMIT:
                $this->limit = null;
                break;
            case self::PART_OFFSET:
                $this->offset = null;
                break;
            case self::PART_HAVING:
                $this->having = null;
                break;
            case self::PART_GROUPBY:
                $this->groupby = null;
                break;
            case self::PART_ORDERBY:
                $this->orderby = null;
                break;
        }

    }

    public function resetAll()
    {
        $this->from = array();
        $this->where = array();
        $this->limit = null;
        $this->offset = null;
        $this->having = null;
        $this->groupby = null;
        $this->orderby = null;
    }

    public function from($from, $fields = array())
    {
        if (is_null($fields)) {
            $fields = array();
        } elseif (is_string($fields)) {
            $fields = array($fields);
        }

        array_unshift($this->from, array($from, $fields));
        return $this;
    }

    public function where($where, $alias = null)
    {
        if (is_null($alias)) {
            $alias = uniqid();
        }
        $this->where[$alias] = $where;
        return $this;
    }
    
    public function whereIn($field, $val_arr, $alias = null)
    {
        $len = count($val_arr);
        for ($i = 0; $i < $len; $i++) {
            $val_arr[$i] = "'".$this->adapter->esc($val_arr[$i])."'";
        }
        $vals_str = implode(', ', $val_arr);
        return $this->where("{$field} IN({$vals_str})", $alias);
    }
    
    public function hasWhereAlias($alias) 
    {
        return isset($this->where[$alias]) ? true : false;
    }
    
    public function resetWhereAlias($alias)
    {
        if ($this->hasWhereAlias($alias)) {
             unset($this->where[$alias]);
             return true;
        }
        return false;
    }

    public function limit($limit)
    {
        if ($limit <= 0) {
            $this->limit = null;
        } else {
            $this->limit = intval($limit);
        }
        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function having($having)
    {
        $this->having = $having;
        return $this;
    }
    
    public function group($group)
    {
        $this->groupby = $group;
        return $this;
    }

    public function order($order)
    {
        $this->orderby = $order;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function joinCross($from, $fields = array())
    {
        $this->_addJoin(self::JOIN_CROSS, $from, $fields, null);
        return $this;
    }

    public function joinInner($from, $conditions, $fields = array())
    {
        $this->_addJoin(self::JOIN_INNER, $from, $fields, $conditions);
        return $this;
    }

    public function joinLeft($from, $conditions, $fields = array())
    {
        $this->_addJoin(self::JOIN_LEFT, $from, $fields, $conditions);
        return $this;
    }

    public function joinRight($from, $conditions, $fields = array())
    {
        $this->_addJoin(self::JOIN_LEFT, $from, $fields, $conditions);
        return $this;
    }

    public function exec()
    {
        $rs = $this->adapter->fetchAll($this->__toString());

        if (($p = $this->getPaginator()) !== null) {
            $p->setCollection($rs);
        }
        return $rs;
    }

    protected function _addJoin($type, $from, $fields, $conds)
    {
        if ($fields == null) {
            $fields = array();
        } elseif (is_string($fields)) {
            $fields = array($fields);
        }
        $bundle = array($type, $from, $fields, $conds);
        array_push($this->joins, $bundle);
    }

    public function __toString()
    {
        $query = $this->_toStringFrom();
        $joins = $this->_toStringJoins();
        if (!empty($joins)) {
            $query .= " {$joins}";
        }
        $where = $this->_toStringWhere();
        if (!empty($where)) {
            $query .= " {$where}";
        }
        $groupby = $this->_toStringGroupBy();
        if (!empty($groupby)) {
            $query .= " {$groupby}";
        }
        $having = $this->_toStringHaving();
        if (!empty($having)) {
            $query .= " {$having}";
        }
        $orderby = $this->_toStringOrderBy();
        if (!empty($orderby)) {
            $query .= " {$orderby}";
        }
        $limit = $this->_toStringLimit();
        if (!empty($limit)) {
            $query .= " {$limit}";
        }
        $offset = $this->_toStringOffset();
        if (!empty($offset)) {
            $query .= " {$offset}";
        }

        return $query;
    }

    protected function _toStringFrom()
    {
        $from_string = "SELECT ";
        $from_fields = $this->_toStringFromFields();
        if (!empty($from_fields)) {
            $from_string .= "{$from_fields}, ";
        }

        $join_fields = $this->_toStringJoinFields();
        if (!empty($join_fields)) {
            $from_string .= "{$join_fields} ";
        } else {
            $from_string = rtrim($from_string, ', ')." ";
        }

        $from_table = $this->_toStringFromTable();
        if (!empty($from_table)) {
            $from_string .= "FROM {$from_table}";
        }

        return $from_string;
    }

    protected function _toStringFromFields()
    {
        $fields_str = "";
        foreach ($this->from as $from_bundle) {
            $from_part = $from_bundle[0];
            $fields = $from_bundle[1];
            $fields_str .= $this->_formatAliasedFields($from_part, $fields);
        }
        return rtrim($fields_str, ", ");
    }

    protected function _toStringJoinFields()
    {
        $fields_str = "";
        foreach ($this->joins as $join_bundle) {
            $from_part = $join_bundle[1];
            $fields = $join_bundle[2];
            $fields_str .= $this->_formatAliasedFields($from_part, $fields).", ";
        }
        return rtrim($fields_str, ", ");
    }

    protected function _formatAliasedFields($from_part, $fields)
    {
        $fields_str = "";
        if (!is_array($from_part)) {
            $table_alias = $from_part;
        } else {
            $v = array_keys(array_to_hash($from_part));
            $table_or_alias = $v[0];
            if ($table_or_alias == $from_part[$table_or_alias]) {
                $table = $table_or_alias;
                $table_alias = $table_or_alias;
            } else {
                $table = $from_part[$table_or_alias];
                $table_alias = $table_or_alias;
            }
        }

        $fields = array_to_hash($fields);
        foreach ($fields as $alias => $field) {
            if($field == '*') {
                $fields_str .= "`{$table_alias}`.*, ";
            } elseif (is_a($field, 'Pfw_Db_Expr')) {
                $fields_str .= "{$field->getValue()} AS `{$alias}`, ";
            } elseif ($field != $alias) {
                $fields_str .= "`{$table_alias}`.`{$field}` AS `{$alias}`, ";
            } else {
                $fields_str .= "`{$table_alias}`.`{$field}`, ";
            }
        }
        return rtrim($fields_str, ", ");
    }

    protected function _toStringFromTable()
    {
        $from_str = "";
        foreach ($this->from as $from_bundle) {
            $from_part = $from_bundle[0];
            if (empty($from_part)) {
                continue;
            }
            if (is_string($from_part)) {
                $from_str .= "`{$from_part}`, ";
            } else {
                $v = array_keys(array_to_hash($from_part));
                $table_or_alias = $v[0];

                if ($table_or_alias == $from_part[$table_or_alias]) {
                    $table = $table_or_alias;
                    $from_str .= "`{$table}`, ";
                } else {
                    $alias = $table_or_alias;
                    $table = $from_part[$table_or_alias];
                    $from_str .= "`{$table}` AS `{$alias}`, ";
                }
            }
        }
        return rtrim($from_str, ", ");
    }

    protected function _toStringJoins()
    {
        $joins_str = "";
        foreach ($this->joins as $join_bundle) {
            $join_type = $join_bundle[0];
            $type_str = $this->join_str[$join_type];

            $from_part = $join_bundle[1];
            $v = array_keys(array_to_hash($from_part));
            $table_or_alias = $v[0];

            if ($table_or_alias == $from_part[$table_or_alias]) {
                $table = $table_or_alias;
                $table_str = "`{$table}`";
            } else {
                $alias = $table_or_alias;
                $table =  $from_part[$table_or_alias];
                $table_str = "`{$table}` AS `{$alias}`";
            }

            $joins_str .= "{$type_str} {$table_str}";
            if (self::JOIN_CROSS == $join_type) {
                continue;
            }

            $join_conds = $join_bundle[3];
            if (!empty($join_conds)) {
                if (is_array($join_conds)){
                    $join_conds = $this->adapter->_sprintfEsc($join_conds);
                }
                $joins_str .= " ON {$join_conds} ";
            }
        }

        return $joins_str;
    }

    protected function _toStringWhere()
    {
        $where_conds = array();
        if (!empty($this->where)) {
            foreach ($this->where as $alias => $where) {
                if (is_array($where)){
                    $where = $this->adapter->_sprintfEsc($where);
                }
                array_push($where_conds, "({$where})");
            }
            return "WHERE ".implode($where_conds, " AND ");
        }
        return "";
    }

    protected function _toStringLimit()
    {
        if (null !== $this->limit) {
            return "LIMIT {$this->limit}";
        }
        return "";
    }

    protected function _toStringOffset()
    {
        if (null !== $this->offset) {
            return "OFFSET {$this->offset}";
        }
        return "";
    }

    protected function _toStringHaving()
    {
        if (null !== $this->having) {
            return "HAVING {$this->having}";
        }
        return "";
    }

    protected function _toStringGroupBy()
    {
        if (null !== $this->groupby) {
            return "GROUP BY {$this->groupby}";
        }
        return "";
    }

    protected function _toStringOrderBy()
    {
        if (null !== $this->orderby) {
            return "ORDER BY {$this->orderby}";
        }
        return "";
    }

    protected static function _optionExists($options, $name)
    {
        return (isset($options[$name]));
    }

    protected static function _getOption($options, $name)
    {
        if (!isset($options[$name])) {
            return null;
        }
        return $options[$name];
    }
}
