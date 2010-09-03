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
 * Defines the methods that all cache providers must implement.
 * 
 * @category      Framework
 * @package       Pfw
 */
interface Pfw_Cache {
    public function __construct($options = array());
    
    /**
     * Is this cache store active?
     *
     * @return bool true if cache store is active, false otherwise
     */
    public function isActive();
    
    /**
     * Gets a value from the cache.
     *
     * @param string $key the key
     * @return mixed the value stored at the key on success, false on failure
     */
    public function get($name);
    
    /**
     * Set a value in the cache even if it already exists.
     *
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param int $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function set($name, $value, $ttl = null);
    
    /**
     * Adds a value to the cache if it doesn't already exist.
     *
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param integer $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function add($name, $value, $ttl = null);
    
    /**
     * Deletes a value from the apc cache.
     *
     * @param string $key the key
     * @return bool true on succcess, false on failure
     */
    public function delete($name);
}
