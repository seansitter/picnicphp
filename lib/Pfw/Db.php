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
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Db
{
    const DEFAULT_ROUTE_NAME = "default";
    protected static $default_router;
    protected static $_supported_adapters = array(
       'mysqli' => 'Mysqli',
       'pdo' => 'Pdo'
    );
    protected static $_connections = array();

    /**
     * Returns an instance of a Pfw_Db_Adapter
     * 
     * @param $route the name of the route
     * @param $reuse
     * @return Pfw_Db_Adapter
     */
    public static function factory($route = null, $reuse = true){
        if (null === $route) {
            if (!isset(self::$default_router)) {
                Pfw_Loader::loadClass('Pfw_Db_Router_Standard');
                self::$default_router = new Pfw_Db_Router_Standard(self::DEFAULT_ROUTE_NAME);
            }

            $route = self::$default_router->getWriteRoute();
        }

        if (true == $reuse) {
            $reuse_key = self::_genCnxKey($route);
            if (isset(self::$_connections[$reuse_key])) {
               self::$_connections[$reuse_key]->isSharedCnx(true);
               return self::$_connections[$reuse_key];
            }
        }

        $adapter_key = strtolower($route['adapter']);
        if (!isset(self::$_supported_adapters[$adapter_key])) {
            Pfw_Loader::loadClass('Pfw_Exception_Db');
            throw new Pfw_Exception_Db("Adapter type {$route['adapter']} is unsupported");
        }

        $class = 'Pfw_Db_Adapter_'.$route['adapter'];
        Pfw_Loader::loadClass($class);
        $instance = new $class($route);

        if (true == $reuse) {
            self::$_connections[$reuse_key] = $instance;
            self::$_connections[$reuse_key]->isSharedCnx(true);
        } else {
            $instance->isSharedCnx(false);
        }

        return $instance;
    }

    /**
     * Returns an instance of a Pfw_Db_Adapter suitable for a transaction
     *
     * @param $route the name of the route
     * @param $reuse
     * @return Pfw_Db_Adapter
     */
    public static function factoryForTxn($route = null)
    {
        return self::factory($route, false);
    }

    private static function _genCnxKey($route){
        return md5($route['adapter'].'::'.$route['username'].'::'.$route['password'].
              '::'.$route['dbname'].'::'.$route['host'].'::'.$route['port'].'::'.$route['socket']);
    }
}
