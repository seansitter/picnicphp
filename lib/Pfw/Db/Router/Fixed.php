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

Pfw_Loader::loadClass('Pfw_Db_Router');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Db_Router_Fixed extends Pfw_Db_Router
{
    protected $route;

    public function __construct($route){
        $this->route = $route;
    }

    public function getReadRoute()
    {
        return $this->route;
    }

    public function getAllReadRoutes()
    {
        return array($this->getReadRoute());
    }

    public function getWriteRoute()
    {
        return $this->getReadRoute();
    }

    public function getAllWriteRoutes()
    {
        return $this->getAllReadRoutes();
    }
}
