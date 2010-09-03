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
 * This class loads the project configuration files
 * and has methods to access the config parameters set
 * in those files
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Config
{
    private static $config = null;

    public static function init()
    {
        self::_initDefaultConfig();
        self::_initEnvConfig();
    }
    
    /**
     * Initializes the complete configuration for the environment
     * specified by $env.
     * 
     * @param string $env
     */
    public static function initForEnv($env)
    {
        self::_initDefaultConfig();
        self::_initEnvConfig($env);
    }

    /**
     * Appends the config file in $file to the current
     * configuration.
     * 
     * @param string $file the config file to append
     */
    public static function appendConfigFile($file)
    {
        if (is_null(self::$config)) {
            self::$config = array();
        }

        $new_config = include($file);
        self::$config = array_merge(self::$config, $new_config);
    }

    /**
     * Appends the array defined by $config to the current 
     * configuration.
     * 
     * @param array $config
     * @return unknown_type
     */
    public static function appendConfig($config)
    {
        if (is_null(self::$config)) {
            self::$config = array();
        }

        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Should never need to be called. Sets the entire config array
     * to the array defined by $config.
     * 
     * @param array $config
     */
    public static function setConfig($config)
    {
        if (!is_array($config)) {
            throw new Pfw_Exception("Config must be array");
        }
        self::$config = $config;
    }

    /**
     * Resets the config.
     */
    public static function reset(){
        self::$config = null;
    }

    /**
     * Should never need to be called. Initializes the default config.
     */
    public static function _initDefaultConfig()
    {
        if (is_null(self::$config)) {
            self::$config = array();
        }

        global $_PATHS;
        $default_config_file = $_PATHS['conf'].DIRECTORY_SEPARATOR."config.default.php";
        $new_config = include($default_config_file);
        self::$config = array_merge(self::$config, $new_config);
    }

    /**
     * Should never need to be called. Initializes the environment
     * specific config.
     * 
     * @param string $env the name of the environment
     */
    public static function _initEnvConfig($env = null)
    {
        if (is_null(self::$config)) {
            self::$config = array();
        }

        global $_PATHS, $_ENVIRONMENT;
        $env = is_null($env) ? $_ENVIRONMENT : $env;
        if (isset($env)) {
            $env_config_file = $_PATHS['conf'].DIRECTORY_SEPARATOR."config.{$env}.php";
            $new_config = include($env_config_file);
            if (!empty($new_config)) {
                self::$config = array_merge(self::$config, $new_config);
            }
        }
    }

    /**
     * Gets the entire config array.
     * 
     * @return array the configuration array
     */
    public static function getConfig()
    {
        if (is_null(self::$config)) {
            self::$config = array();
        }
        return self::$config;
    }

    /**
     * Gets the value of a configuration parameter $name.
     * 
     * @param string $name the configuration parameter
     * @return mixed
     */
    public static function get($name)
    {
        $config = self::getConfig();
        if (!isset($config[$name])) {
            return null;
        }
        return $config[$name];
    }
   
    /**
     * Sets the value of a configuration parameter.
     * 
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value)
    {
        self::$config[$name] = $value;
    }
}
