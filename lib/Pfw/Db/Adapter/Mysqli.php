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

Pfw_Loader::loadClass('Pfw_Db_Adapter');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Db_Adapter_Mysqli extends Pfw_Db_Adapter
{
    protected $connected = false;
    protected static $schemas = array();
    protected $route = null;

    protected $type_arr = array(
        'int'         => self::TYPE_INTEGER,
        'tinyint'     => self::TYPE_INTEGER,
        'smallint'    => self::TYPE_INTEGER,
        'mediumint'   => self::TYPE_INTEGER,
        'bigint'      => self::TYPE_INTEGER,
        'boolean'     => self::TYPE_INTEGER,
        'bit'         => self::TYPE_INTEGER,
        'decimal'     => self::TYPE_FLOAT,
        'dec'         => self::TYPE_FLOAT,
        'double'      => self::TYPE_FLOAT,
        'float'       => self::TYPE_FLOAT,
        'binary'      => self::TYPE_BINARY,
        'blob'        => self::TYPE_BINARY,
        'tinyblob'    => self::TYPE_BINARY,
        'mediumblob'  => self::TYPE_BINARY,
        'longblob'    => self::TYPE_BINARY,
        'varbinary'   => self::TYPE_BINARY,
        'char'        => self::TYPE_CHARACTER,
        'varchar'     => self::TYPE_VARCHAR,
        'text'        => self::TYPE_TEXT,
        'tinytext'    => self::TYPE_TEXT,
        'mediumtext'  => self::TYPE_TEXT,
        'longtext'    => self::TYPE_TEXT,
        'set'         => self::TYPE_ENUM,
        'enum'        => self::TYPE_ENUM,
        'timestamp'   => self::TYPE_TIME,
        'time'        => self::TYPE_TIME,
        'year'        => self::TYPE_DATETIME,
        'datetime'    => self::TYPE_DATETIME,
        'date'        => self::TYPE_DATETIME
    );

    public function __construct($route)
    {
        $this->route = $this->initRoute($route);
        $this->link = mysqli_init();
    }

    protected function initRoute($route){
        if (empty($route['port'])) {
            $route['port'] = null;
        }
        if (empty($route['socket'])) {
            $route['socket'] = null;
        }
        return $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }

        @mysqli_real_connect(
          $this->link,
          $this->route['host'],
          $this->route['username'],
          $this->route['password'],
          $this->route['dbname'],
          $this->route['port'],
          $this->route['socket']
        );
        $this->throwErrors();

        $this->connected = true;
    }

    /**
     * Begin a new transaction
     */
    public function beginTxn()
    {
        $this->setAutoCommit(false);
        $this->query("START TRANSACTION");
    }

    /**
     * Commit the current transaction
     */
    public function commitTxn()
    {
        $this->query("COMMIT");
        $this->setAutoCommit(true);
    }

    /**
     * Rollback the current transaction
     */
    public function rollbackTxn()
    {
        $this->query("ROLLBACK");
        $this->setAutoCommit(true);
    }

    /**
     * Should we autocommit our statements?
     *
     * @param bool $autocommit
     */
    public function setAutoCommit($autocommit) {
        $ac = ($autocommit) ? 1 : 0;
        $this->query("SET autocommit = {$ac}");
    }

    public function disconnect()
    {
        mysqli_close($this->link);
        $this->link = null;
        $this->connected = false;
    }

    public function fetchOne($sql)
    {
        if (is_array($sql)) {
            $sql = $this->_sprintfEsc($sql);
        }
        $result = $this->query($sql);
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        return $row;
    }

    public function fetchAll($sql)
    {
        if (is_array($sql)) {
            $sql = $this->_sprintfEsc($sql);
        }
        $rows = array();
        $result = $this->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
        return $rows;
    }

    public function update($sql)
    {
        if (is_array($sql)) {
            $sql = $this->_sprintfEsc($sql);
        }
        $this->query($sql);
        return mysqli_affected_rows($this->link);
    }

    public function insert($sql)
    {
        if (is_array($sql)) {
            $sql = $this->_sprintfEsc($sql);
        }
        $this->query($sql);
        return mysqli_insert_id($this->link);
    }

    public function insertAll($sql)
    {
        if (is_array($sql)) {
            $sql = $this->_sprintfEsc($sql);
        }
        $this->query($sql);
        return mysqli_affected_rows($this->link);
    }
    
    public function insertOrUpdateTable($table, $unique_name, $unique_value, $data_insert, $data_update = null)
    {
        if (is_null($data_update)) {
            $data_update = $data_insert;
        }
        unset($data_inser['dt_created']);
        
        list($insert_fields_part, $insert_data_part) = $this->formatInsertData($data_insert);
        $update_data_part = $this->formatUpdateData($data_update);
        
        $stmt = "INSERT INTO `{$table}` {$insert_fields_part} VALUES {$insert_data_part} ".
            "ON DUPLICATE KEY UPDATE {$update_data_part}";

        return $this->query($stmt);
    }

    public function delete($sql)
    {
        return $this->update($sql);
    }

    public function getTableSchema($table)
    {
        if (empty($table)) {
            return array();
        }

        // try static, local caches
        if (isset(self::$schemas[$table])) {
            return self::$schemas[$table];
        }
        if (false !== ($schema = Pfw_Cache_Local::get('schema_'.$table))) {
            return $schema;
        }

        $rs = $this->fetchAll("DESCRIBE `{$table}`");
        $schema = array();
        #print_r($rs);
        foreach ($rs as $d) {
            $f = $d['Field'];

            $tls = $this->_descTypeLenSign($d['Type']);
            $schema[$f]['type'] = $tls[0];
            $schema[$f]['length'] = $tls[1];
            $schema[$f]['sign'] = $tls[2];

            $schema[$f]['default'] = $this->_descDef($d['Default']);
            $schema[$f]['key'] = $this->_descKey($d['Key']);
            $schema[$f]['nullable'] = $this->_descIsNullable($d['Null']);
            $schema[$f]['extra'] = $this->_descExtra($d['Extra']);
        }

        // cache back
        self::$schemas[$table] = $schema;
        Pfw_Cache_Local::set('schema_'.$table, $schema);

        return $schema;
    }

    protected function _descTypeLenSign($type)
    {
        $m = array();
        if (preg_match('/(\w+)\((\d+)\)\s?(\w+)?/', $type, $m)) {
            array_shift($m);
            $m[0] = $this->type_arr[$m[0]];

            if(isset($m[1])) {
                $m[1] = intval($m[1]);
            } else {
                $m[1] = null;
            }

            if (($m[0] == self::TYPE_INTEGER) or ($m[0] == self::TYPE_FLOAT)) {
                if (isset($m[2]) and ($m[2] == "unsigned")) {
                    $m[2] = self::SIGN_UNSIGNED;
                } else {
                    $m[2] = self::SIGN_SIGNED;
                }
            } else {
                $m[2] = null;
            }


            return $m;
        }

        if (strpos($type, 'decimal') === 0) {
            return array(self::TYPE_FLOAT, null, null);
        }
        return array($this->type_arr[$type], null, null);
    }

    protected function _descIsNullable($is_nullable)
    {
        return ($is_nullable == "YES") ? true : false;
    }

    protected function _descDef($def)
    {
        return $def;
    }

    protected function _descKey($key)
    {
        return $key;
    }

    protected function _descExtra($extra)
    {
        return $extra;
    }

    public function query($sql)
    {
        $this->connect();
        $this->addHistory($sql);
        $ret = mysqli_query($this->link, $sql);
        $this->throwErrors($sql);
        return $ret;
    }

    protected function throwErrors($sql = null)
    {
       if (($errno = mysqli_errno($this->link)) !== 0) {
           $errstr = mysqli_error($this->link);
           Pfw_Loader::loadClass('Pfw_Exception_Db_Mysqli');
           $msg = "Mysqli operation failed with errno: $errno, ".
               "message: '$errstr'";
           if (null !== $sql) { $msg = $msg . ", sql: $sql"; }
           throw new Pfw_Exception_Db_Mysqli($msg, $errno);
       }
    }

    public function getSelectBuilder()
    {
        Pfw_Loader::loadClass('Pfw_Db_Select_Mysqli');
        return new Pfw_Db_Select_Mysqli($this);
    }

    public function esc($str)
    {
        $this->connect();
        return mysqli_real_escape_string($this->link, $str);
    }
    
    public function quoteIdentifier($identifier)
    {
        return "`$identifier`";
    }

    public function getLinkId()
    {
        return mysqli_thread_id($this->getLink());
    }

    private function isConnected()
    {
       return ($this->connected !== false);
    }
}
