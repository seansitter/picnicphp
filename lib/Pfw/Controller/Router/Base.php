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

Pfw_Loader::loadClass('Pfw_Regex');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
abstract class Pfw_Controller_Router_Base
{
    const ROUTE_PART_LIT = 0;
    const ROUTE_PART_VAR = 1;
    const ROUTE_PART_WILD = 2;

    protected $routes = null;
    protected $compiled_routes = null;
    protected $success_route_name = null;

    protected $modules = null;

    abstract function route($path);
    abstract function urlFor($route_name, $route_params = array());

    public function __construct($routes = null, $modules = null)
    {
        if (!is_null($routes)) {
            $this->setRoutes($routes);
        } else {
            $this->setRoutes(array());
        }
        if (!is_null($modules)) {
            $this->setModules($modules);
        } else {
            $this->setRoutes(array());
        }
    }

    public function setSuccessRouteName($name)
    {
        $this->success_route_name = $name;
    }

    public function getSuccessRouteName()
    {
        return $this->success_route_name;
    }

    protected function splitPath($path)
    {
        $spath = array();
        $tok = strtok($path, '/');

        while ($tok !== false) {
            array_push($spath, $tok);
            $tok = strtok('/');
        }

        return $spath;
    }

    public function setRoutes($routes)
    {
        $this->routes = $routes;
        return $this;
    }

    public function appendRoute($route)
    {
        $this->routes = array_merge($this->routes, $route);
        return $this;
    }

    public function prependRoute($route)
    {
        $this->routes = array_merge($route, $this->routes);
        return $this;
    }

    public function deleteRoute($route_name)
    {
        $ct = count($this->routes);
        for ($i == 0; $i < $ct; $i++) {
            if ($this->routes[$i]['name'] == $route_name) {
                array_splice($this->routes, $i, 1);
                break;
            }
        }
        return $this;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;
        return $this;
    }

    public function appendModule($module_name)
    {
        array_push($this->modules, $module_name);
        return $this;
    }

    public function prependModule($module_name)
    {
        array_unshift($this->modules, $module_name);
        return $this;
    }

    public function isModule($module_name)
    {
        if (is_array($this->modules)) {
            return in_array($module_name, $this->modules);
        }
        return false;
    }
}
