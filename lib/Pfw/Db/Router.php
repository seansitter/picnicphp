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
abstract class Pfw_Db_Router
{
    abstract public function getReadRoute();
    abstract public function getAllReadRoutes();
    abstract public function getWriteRoute();
    abstract public function getAllWriteRoutes();

    protected $route_name;

    public function __construct ($route_name)
    {
        $this->route_name = $route_name;
    }

    public function getRouteName()
    {
        return $this->route_name;
    }
}
