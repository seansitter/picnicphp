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
 * Wraps the distributed cache driver.
 * 
 * The distributed cache is not specifically a local cache.
 * Though instances themselves may be local, the cache is shared
 * among application instances. This class a wrapper for a specific
 * implementation, such as Pfw_Memcache, though others may be provided.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Cache_Dist {
    private static $instance = null;

    /**
     * Resets the instance of the cache driver
     */
    public static function _reset()
    {
        self::$instance = null;
    }

    /**
     * Returns an instance of the distributed cache driver
     * 
     * @return Pfw_Cache|null instance of a Pfw_Cache object or null if none
     * is configured
     */
    public static function getInstance()
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        if (null == ($dist_cache = Pfw_Config::get('dist_cache'))) {
            return null;
        }

        if (is_array($dist_cache)) {
            $class = $dist_cache['class'];
            unset($dist_cache['class']);
            $options = $dist_cache;
        } else {
            $class = $dist_cache;
            $options = array();
        }
        
        Pfw_Loader::loadClass($class);
        self::$instance = new $class($options);

        return self::$instance;
    }

    /**
     * @param string $key the key
     * @see Pfw_Cache::get()
     */
    public static function get($key)
    {
        if (!self::isActive()) {
            return false;
        }
        return self::getInstance()->get($key);
    }

    /**
     * @see Pfw_Cache::set()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param int $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public static function set($key, $value, $ttl_s = null)
    {
        if (!self::isActive()) {
            return false;
        }
        return self::getInstance()->set($key, $value, $ttl_s);
    }
    
    /**
     * @see Pfw_Cache::add()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param integer $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public static function add($key, $value, $ttl_s = null)
    {
        if (!self::isActive()) {
            return false;
        }
        return self::getInstance()->add($key, $value, $ttl_s);
    }
    
    /**
     * @see Pfw_Cache::delete()
     * @param string $key the key
     * @return bool true on succcess, false on failure
     */
    public static function delete($key)
    {
        if (!self::isActive()) {
            return false;
        }
        return self::getInstance()->delete($key);
    }

    /**
     * @see Pfw_Cache::isActive()
     * @return bool true if cache store is active, false otherwise
     */
    public static function isActive()
    {
        if (null === self::getInstance()) {
            return false;
        }
        return true;
    }
    
    /**
     * Is our instance of the distributed cache driver active?
     * 
     * @return bool
     */
    public function isInstanceActive()
    {
        return self::$instance->isActive();
    }
}
