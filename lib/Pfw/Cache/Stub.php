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
 * Stub cache driver, used in testing. Sets and gets instance variables.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Cache_Stub implements Pfw_Cache
{
    public $options;
    public $data;

    public function __construct ($options = array())
    {
        $this->options = $options;
        $this->data = array();
    }

    /**
     * @see Pfw_Cache::isActive()
     * @return bool true if cache store is active, false otherwise
     */
    public function isActive()
    {
        return true;
    }
    
    /**
     * @see Pfw_Cache::get()
     * @param string $key the key
     */
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        return $this->data[$key];
    }
    
    /**
     * @see Pfw_Cache::set()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param int $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function set($key, $value, $ttl = 300)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * @see Pfw_Cache::add()
     * @param string $key the key
     * @param mixed $value the value to store at key
     * @param integer $ttl time to live in seconds
     * @return bool true on success, false on failure
     */
    public function add($key, $value, $ttl = 300)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * @see Pfw_Cache::delete()
     * @param string $key the key
     * @return bool true on succcess, false on failure
     */
    public function delete($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }
}
