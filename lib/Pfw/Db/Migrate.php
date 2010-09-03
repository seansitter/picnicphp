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
Pfw_Loader::loadClass('Pfw_Db');
Pfw_Loader::loadClass('Pfw_Db_Router_Standard');

class Pfw_Db_Migrate
{
    public static $_dry_run = false;

    public static function forward($statements, $route_name = 'default')
    {
        return self::_do($statements, false, $route_name);
    }
    
    public static function back($statements, $route_name = 'default')
    {
        return self::_do($statements, true, $route_name);
    }
    
    protected static function _do($statements, $force, $route_name)
    {
        $router = new Pfw_Db_Router_Standard($route_name);
        $routes = $router->getAllWriteRoutes();

        foreach ($routes as $route) {
            $db = Pfw_Db::factory($route, false);
            foreach ($statements as $statement) {
                echo "running sql: \"$statement\"\n";
                if (false == self::$_dry_run){
                    if ($force) {
                        try {
                            $out = $db->query($statement);
                        } catch(Exception $e) {}
                    } else {
                        $out = $db->query($statement);
                    }
                }
            }
        }
    }
}