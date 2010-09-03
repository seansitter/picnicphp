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

Pfw_Loader::loadClass('Pfw_Cache');

/**
 * A Memcache driver implementation of Pfw_Cache, generally
 * used as a provider class for Pfw_Cache_Dist.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Cache_Memcache implements Pfw_Cache
{
    // default option constants	
    const PERSISTENT = true;
    const DEFAULT_TTL_S = 300;
    const WEIGHT = 1;
    const GZIP_COMPRESS = 0;
    const TIMEOUT = 1;
    const RETRY_INTERVAL = 15;
    const STATUS = true;
    const DEFAULT_PORT = 11211;

    private $instance = null;
    private $options = array();

    /**
     * @param array $options <pre>contains keys
     * 'gzip' 0 or 1 whether to gzip compress our data
     * 'default_ttl_s default time to live in seconds
     * 'servers' array containing arrays of
     *      'host'    point to the host where memcached is listening for connections. 
     *                this parameter may also specify other transports like 
     *                unix:///path/to/memcached.sock  to use UNIX domain sockets, 
     *                in this case port  must also be set to 0. 
     *      'port'    point to the port where memcached is listening for connections. 
     *                this parameter is optional and its default value is 11211.
     *                set this parameter to 0 when using UNIX domain sockets. 
     *      'persist' controls use of persistent connection, default true
     *      'weight'  number of buckets to create for this server which in turn 
     *                control its probability of it being selected. 
     *                the probability is relative to the total weight of all servers. 
     *      'timeout' value in seconds which will be used for connecting to the daemon. 
     *                think twice before changing the default value of 1 second - you can 
     *                lose all the advantages of caching if your connection is too slow. 
     *      'retry_interval' controls how often a failed server will be retried, 
     *                the default value is 15 seconds. Setting this parameter to 
     *                -1 disables automatic retry. Neither this nor the persistent 
     *                parameter has any effect when the extension is loaded dynamically via dl(). 
     *                each failed connection struct has its own timeout and before it has expired 
     *                the struct will be skipped when selecting backends to serve a request. o
     *                once expired the connection will be successfully reconnected or marked as 
     *                failed for another retry_interval seconds. the typical effect is that each 
     *                web server child will retry the connection about every retry_interval 
     *                seconds when serving a page.
     * </pre>
     */
    public function __construct($options = array())
    {
        if (empty($options) or !isset($options['servers'])) {
            return;
        }
        
        // if memcache module isn't loaded, we can't do anything
        if (!class_exists('Memcache', false)) {
            return;
        }
        
        // setup config options
        $this->options['gzip'] = isset($options['gzip']) ? $options['gzip'] : self::GZIP_COMPRESS;
        $this->options['default_ttl_s'] = isset($options['default_ttl_s']) ? $options['default_ttl_s'] : self::DEFAULT_TTL_S;
        
        // initialize with servers
        $this->initialize($options['servers']);
    }

    /**
     * Internal initialize method.
     * 
     * @param array $server_list
     */
    private function initialize($server_list)
    {
        $connect_count = 0;
        if (count($server_list) > 0) {
            $this->instance = new Memcache();

            // add all of the servers
            foreach ($server_list as $server) {
                if (!isset($server['host']) or empty($server['host'])) {
                    Pfw_Loader::loadClass('Pfw_Exception_System');
                    throw new Pfw_Exception_System('Must minimally specify a host for each server in memcache list.');
                }
                if (0 === strpos($server['host'], 'unix:///')) {
                    $server['port'] = 0;
                } elseif (!isset($server['port'])) {
                    $server['port'] = self::DEFAULT_PORT;
                }
                if (!isset($server['persist'])) {
                    $server['persist'] = self::PERSISTENT;
                }
                if (!isset($server['weight'])) {
                    $server['weight'] = self::WEIGHT;
                }
                if (!isset($server['timeout'])) {
                    $server['timeout'] = self::TIMEOUT;
                }
                if (!isset($server['retry_interval'])) {
                    $server['retry_interval'] = self::RETRY_INTERVAL;
                }

                // add the server
                $add_status = $this->instance->addServer(
                    $server['host'],
                    $server['port'],
                    $server['persist'],
                    $server['weight'],
                    $server['timeout'],
                    $server['retry_interval'],
                    true
                );

                if ($this->options['debug_mode']) {
                    $health = $this->instance->getServerStatus($server['host'], $server['port']);
                    trigger_error("memcached is running on {$server['host']}:{$server['port']}: $health", E_USER_NOTICE);
                }

                if (false === $add_status) {
                    trigger_error("failed to add memcache server: {$server['host']}:{$server['port']}", E_USER_WARNING);
                } else {
                    // trigger_error("added memcache server: {$server['host']}:{$server['port']}", E_USER_NOTICE);
                    $connect_count += 1;
                }

                if ($connect_count <= 0) {
                    $this->instance = null;
                }
            }
        } else {
            $this->instance = null;
        }
    }

    /**
     * @see Pfw_Cache::add()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param integer $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function add($key, $var, $ttl_s = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        if (null === $ttl_s) {
            $ttl_s = $this->options['default_ttl_s'];
        }
        return $this->instance->add($key, $var, $this->options['gzip'], $ttl_s);
    }

    /**
     * @see Pfw_Cache::set()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param int $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function set($key, $var, $ttl_s = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        if (null === $ttl_s) {
            $ttl_s = $this->options['default_ttl_s'];
        }

        return $this->instance->set($key, $var, $this->options['gzip'], $ttl_s);
    }

    /**
     * @see Pfw_Cache::get()
     * @param string $key the key
     */
    public function get($key)
    {
        if (!$this->isActive()) {
            return false;
        }
        return $this->instance->get($key);
    }
    
    /**
     * @see Pfw_Cache::delete()
     * @param string $key the key
     * @return bool true on succcess, false on failure
     */
    public function delete($key)
    {
        if (!$this->isActive()) {
            return false;
        }
        return $this->instance->delete($key, $ttl_s);
    }

    /**
     * @see Pfw_Cache::isActive()
     * @return bool true if cache store is active, false otherwise
     */
    public function isActive()
    {
        return ($this->instance === null) ? false : true;
    }
    
    /**
     * Gets the server statatus of a particular node in the distrubuted
     * cache cluster.
     * 
     * @param string $host
     * @param string|int $port
     * @return integer server status, 0 if failed, non-zero otherwise
     */
    public function getServerStatus($host, $port = null)
    {
        return $this->instance->getServerStatus($host, $port);
    }
}
