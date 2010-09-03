<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @category      Framework
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
class Pfw_PluginManager
{
    protected static $conf_plugins = null;
    protected static $plugins = null;
    protected static $phases = array(
        'preRoute',
        'postRoute',
        'preMap',
        'postMap',
        'preDispatch',
        'postDispatch'
    );
    protected static $initialized = false;
    const CACHE_TTL_S = 300;

    public function init($file = "plugins.xml")
    {
        self::$initialized = true;

        if (!is_null(self::$plugins)) {
            return self::$plugins;
        }

        $classes_key = md5('_pfw_plugins_classes_'.$file);
        $cache_key = md5('_pfw_plugins_'.$file);
        
        if (false !== ($class_list = Pfw_Cache_Local::get($classes_key))) {
        	// load all of the classes we're about to deserialize
            foreach ($class_list as $class) {
                Pfw_Loader::loadClass($class);
            }
            if (false !== (self::$plugins = Pfw_Cache_Local::get($cache_key))) {
                return true;
            }
        } 
        
        self::$plugins = array();
        $class_list = array();
        foreach (self::$phases as $phase) {
            self::$plugins[$phase] = array();
        }

        global $_PATHS;
        $file = $_PATHS['conf'].DIRECTORY_SEPARATOR.$file;

        try {
            $conf_plugins = simplexml_load_file($file);
        } catch (Exception $e) {
            throw new Pfw_Exception_System("Failed to parse plugin config file: $file");
        }

        if (!empty($conf_plugins)) {
            foreach ($conf_plugins as $plugin) {
                $attr = $plugin->attributes();
                $class = (string)$attr['class'];
                Pfw_Loader::loadClass($class);
                array_push($class_list, $class);
                $inst = new $class();
                $methods = get_class_methods($inst);
                foreach ($methods as $method) {
                    if (in_array($method, self::$phases)) {
                        $name = isset($attr['name']) ? (string)$attr['name'] : null;
                        $i = &$inst;
                        array_push(
                            self::$plugins[$method],
                            array('inst' => $i, 'name' => $name)
                        );
                    }
                }
            }
        }

        Pfw_Cache_Local::set($classes_key, $class_list, self::CACHE_TTL_S);
        Pfw_Cache_Local::set($cache_key, self::$plugins, self::CACHE_TTL_S);
        
        return true;
    }

    public static function initFromCache($cache_plugins)
    {
        foreach($cache_plugins as $phase => $plugins) {
            foreach ($plugins as $plugin){
                $class = $plugin['class'];
                Pfw_Loader::loadClass($class);
                $inst = new $class();
                $i = &$inst;
                array_push(
                    self::$plugins[$phase],
                    array('inst' => $i, 'name' => $plugin['name'])
                );
            }
        }
        objp(self::$plugins);
        return true;
    }

    public static function execPreRoute()
    {
        return self::execPhase('preRoute');
    }

    public static function execPostRoute()
    {
        return self::execPhase('postRoute');
    }

    public static function execPreMap()
    {
        return self::execPhase('preMap');
    }

    public static function execPostMap()
    {
        return self::execPhase('postMap');
    }

    public static function execPreDispatch()
    {
        return self::execPhase('preDispatch');
    }

    public static function execPostDispatch()
    {
        return self::execPhase('postDispatch');
    }

    protected function execPhase($phase)
    {
        if (!self::$initialized) {
            return false;
        }

        foreach (self::$plugins[$phase] as $plugin) {
            $plugin['inst']->$phase();
        }

        return true;
    }
}
