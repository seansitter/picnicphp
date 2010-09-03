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

Pfw_Loader::loadClass('Pfw_Db');
Pfw_Loader::loadClass('Pfw_Db_Expr');
Pfw_Loader::loadClass('Pfw_Db_Router_Standard');
Pfw_Loader::loadClass('Pfw_Cache_Dist');
Pfw_Loader::loadClass('Pfw_Session_Handler');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Session_DistCacheDb implements Pfw_Session_Handler {
    const CACHE_PREFIX = "_pfw_sess_";
    const CACHE_TTL = 600;
    const DEFAULT_DB_TABLE = 'sessions';
    
    protected $config;
    protected $sessions = array();
    /**
     * @var Pfw_Db_Adapter
     */
    protected $db;
    protected $db_table;
    
    public function __destruct()
    {
        session_write_close();
        unset($this->db);
        unset($this->sessions);
        unset($this->db_table);
        unset($this->config);
    }
    
    public function open($save_path, $session_name)
    {
        $this->config = Pfw_Config::get('session');
        $db_route_name = $this->config['handler']['db_route_name'];
        
        $db_router = new Pfw_Db_Router_Standard($db_route_name);
        $db_route = $db_router->getWriteRoute();
        $this->db = Pfw_Db::factory($db_route, true);
        $this->sessions = array();
        $this->db_table = isset($this->config['handler']['db_table']) ? 
            $this->config['handler']['db_table'] : self::DEFAULT_DB_TABLE;
        
        return true;
    }
    
    public function close()
    {
        return true;
    }
    
    public function read($id)
    {
        if (isset($this->sessions[$id])) {
            return (string)$this->sessions[$id];
        }

        $cache_key = $this->cacheKey($id);
        if (false !== ($sess = Pfw_Cache_Dist::get($cache_key))) {
            $this->sessions[$id] = $sess;
            return (string)$sess;
        }
        
        $where = $this->db->_sprintfEsc(array('session_id = %s', $id), true);
        $data = $this->db->fetchOne(
            "SELECT `session_data` FROM `{$this->db_table}` WHERE {$where}"
        );
        
        $session_data = (string)$data['session_data'];
        $this->sessions[$id] = $session_data;
        Pfw_Cache_Dist::set($cache_key, $session_data, self::CACHE_TTL);
        
        return $session_data;
    }
    
    public function write($id, $sess_data)
    {
        // if the session data hasn't changed, don't rewrite it
        if (isset($this->sessions[$id])) {
            if ($this->sessions[$id] == $sess_data) {
                return true;
            } 
        }
        
        $this->sessions[$id] = $sess_data;
        Pfw_Cache_Dist::set($this->cacheKey($id), $sess_data, self::CACHE_TTL);

        $this->db->insertOrUpdateTable(
            $this->db_table,
            'session_id', $id,
            array('session_id' => $id, 'session_data' => $sess_data, 'dt_created' => new Pfw_Db_Expr("NOW()")),
            array('session_data' => $sess_data)
        );
        
        return true;
    }
    
    public function permify($id) {
        $this->db->insertOrUpdateTable(
            $this->db_table,
            'session_id', $id,
            array('session_id' => $id, 'permified' => '1', 'dt_created' => new Pfw_Db_Expr("NOW()")),
            array('permified' => '1')
        );
        Pfw_Cache_Dist::delete($this->cacheKey($id));
    }
    
    public function destroy($id)
    {
        if (isset($this->sessions[$id])) {
            unset($this->sessions[$id]);
        }
        Pfw_Cache_Dist::delete($this->cacheKey($id));
        
        $this->db->deleteFromTable($this->db_table, array('session_id = %s', $id));
    }
    
    public function renew($id)
    {
        return true;
    }
    
    public function gc($max_lifetime_s)
    {
        return true;
    }
    
    protected function cacheKey($id)
    {
        return self::CACHE_PREFIX."_{$id}";
    }
}
