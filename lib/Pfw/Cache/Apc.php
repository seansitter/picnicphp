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
 * An APC driver implementation of Pfw_Cache, generally
 * used as a provider class for Pfw_Cache_Local.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Cache_Apc implements Pfw_Cache
{
	// default option constants
    const DEFAULT_TTL_S = 300;

    protected static $apc_exists = null;
    private $options;

    /**
     * @param array $options containing keys
     * 'default_ttl_s' for default ttl in seconds 
     */
    public function __construct($options = array()) {
        $this->options = $options;

        if (!isset($this->options['default_ttl_s'])) {
            $this->options['default_ttl_s'] = self::DEFAULT_TTL_S;
        }
    }

    
    /**
     * @param string $key the key
     * @see Pfw_Cache::get()
     */
    public function get($key)
    {
        if (!$this->isActive()) {
            // apc isn't running
            return false;
        }

        if (false !== ($value = apc_fetch($key))) {
            // value exists, return it
            return unserialize($value);
        }

        // value didn't exist
        return false;
    }
    

    /**
     * @see Pfw_Cache::set()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param int $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function set($key, $value, $ttl_s = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        if (null === $ttl_s) {
            $ttl_s = $this->options['default_ttl_s'];
        }

        return apc_store($key, serialize($value), $ttl_s);
    }

    
    /**
     * @see Pfw_Cache::add()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param integer $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function add($key, $value, $ttl_s = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        if (null === $ttl_s) {
            $ttl_s = $this->options['default_ttl_s'];
        }

        return apc_add($key, serialize($value), $ttl_s = null);
    }

    
    /**
     * @see Pfw_Cache::delete()
     * @param string $key the key
     * @return bool true on succcess, false on failure
     */
    public function delete($key)
    {
        if (!self::isActive()) {
            return false;
        }

        return apc_delete($key);
    }


    /**
     * @see Pfw_Cache::isActive()
     * @return bool true if cache store is active, false otherwise
     */
    public function isActive()
    {
        if (null === self::$apc_exists) {
            self::$apc_exists = (function_exists('apc_fetch')) ? true : false;
        }
        return self::$apc_exists;
    }
}
