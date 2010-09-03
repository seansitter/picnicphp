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

Pfw_Loader::loadClass('Pfw_Config');

/**
 * Wrapper class to simplify session management
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Session
{
    // @var bool
    protected static $initialized = false;
    // @var bool
    protected static $is_renewing = null;
    // @var Pfw_Session_Handler
    protected static $adapter = null;
    
    const SESS_PERM_KEY = '_pfw_permified';
    
    /**
     * Starts a new session. Generally happens in prj_startup.php.
     * Should happen once per request. 
     */
    public static function start()
    {
        if (self::isStarted()) {
            return false;
        }
        $config = Pfw_Config::getConfig();
        if (!isset($config['session'])) {
            $config['session'] = array();
        }
        
        // maybe accept ini params in args and set here?
        // ...
        
        if (ini_get('session.auto_start')) {            
            // reset session if autostarted in ini
            error_log(
                "A session handler is configured, but session was autostarted in ".
                "ini with session.auto_start, resetting for normal session handling."
            );
            session_write_close(); 
        }
            
        if (isset($config['session']['handler_class'])) {
            self::setupHandler($config['session']['handler_class']);
        }
        
        // this is the stuff for default handler
        else {
            if (isset($config['session']['save_path'])) {
                session_save_path($config['session']['save_path']);
            }
        }
        
        if (isset($config['session']['lifetime_s'])) {
            $lifetime_s = intval($config['session']['lifetime_s']);
            ini_set('session.cookie_lifetime', $lifetime_s);
            if (isset($config['session']['renew']) and (false == $config['session']['renew'])) {
                // not renewing, absolute max lifetime
                self::$is_renewing = false;
            }
            else {
                // renewing
                self::$is_renewing = true;
            }   
        }
        
        session_start();
        
        // if you're going to mess with the cookies, its gotta be
        // after the session as started
        if (self::$is_renewing) {
            self::renew($lifetime_s);
        }
        
        self::$initialized = true;
    }
    
    /**
     * Sets up a session handler
     * @param string $class name of the handler class 
     */
    protected static function setupHandler($class)
    {
        Pfw_Loader::loadClass($class);
        $a = new $class();
        session_set_save_handler(
            array($a, 'open'),
            array($a, 'close'),
            array($a, 'read'),
            array($a, 'write'),
            array($a, 'destroy'),
            array($a, 'gc')
        );
        self::$adapter = $a;
    }
    
    /**
     * Returns the handler instance, if a handler is used
     * @return Pfw_Session_Handler
     */
    protected static function getHandler()
    {
        return self::$adapter;
    }
    
    /**
     * Renews the session
     * @param int $seconds number of seconds to extend session
     */
    public static function renew($seconds)
    {
        if (!self::get(self::SESS_PERM_KEY)) {
            self::getHandler()->renew(session_id());
            self::_renew(intval($seconds));
        }
    }
    
    /**
     * Makes the session permanent
     * @return unknown_type
     */
    public static function permify()
    {
        if (!self::get(self::SESS_PERM_KEY)) {
            self::set(self::SESS_PERM_KEY, 1);
            self::getHandler()->permify(session_id());
            self::_renew();
            return true;
        }
        return false;
    }
    
    /**
     * Is the session permanent (through permify)
     * @return bool
     */
    public static function isPermified()
    {
        return self::get(self::SESS_PERM_KEY) === 1 ? true : false;
    }
    
    /**
     * Renews the session
     * @param int $seconds
     */
    private static function _renew($seconds = null)
    {
        if (is_null($seconds)) {
            // renew for another 5 years
            $seconds = 365 * 5 * 60 * 60 * 24;            
        }
        setcookie(session_name(), session_id(), time() + $seconds, '/');
    }
    
    /**
     * Has the session been started?
     * @return book
     */
    public static function isStarted()
    {
        return self::$initialized;
    }

    /**
     * Sets a session variable
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = serialize($value);
    }

    /**
     * Gets a session variable
     * @param string $key
     * @return mixed|null
     */
    public static function get($key)
    {
        if(self::exists($key)) {
            return unserialize($_SESSION[$key]);
        }
        return null;
    }

    /**
     * Clears  a session variable
     * @param string $key
     * @return bool true if the key existed and was clear, false otherwise
     */
    public static function clear($key)
    {
        $set = isset($_SESSION[$key]);
        if($set){
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * Clears all session variables
     */
    public static function clearAll()
    {
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroys the session
     */
    public static function destroy()
    {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }

        session_destroy();
    }

    /**
     * 
     * @param unknown_type $key
     * @return unknown_type
     */
    public static function exists($key)
    {
        return array_key_exists($key, $_SESSION) ? true : false;
    }

    /**
     * Calls session_write_close to end the session
     */
    public static function end()
    {
        session_write_close();
    }
}
