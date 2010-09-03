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
 * Front controller executes the router and dispatches the request
 * 
 * @category      Framework
 * @package       Pfw
 */
final class Pfw_Controller_Front
{
    private static $instance = null;
    private $router_inst = null;
    private $cont_act_mapper = null;


    /**
     * Singleton instance
     *
     * @return Pfw_Controller_Front
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Gets an instance of the router, or Pfw_Controller_Router_Standard if none
     *
     * @return Pfw_Controller_Router_Base $router_inst
     */
    public function getRouter()
    {
        if (null === $this->router_inst ){
            Pfw_Loader::loadClass('Pfw_Controller_Router_Standard');
            $this->router_inst = new Pfw_Controller_Router_Standard();
        }
        return $this->router_inst;
    }


    /**
     * Sets the router instance, defaults to Pfw_Controller_Router_Standard
     *
     * @param Pfw_Controller_Router_Base $router_inst
     * @return Pfw_Controller_Router_Base if a router was previously set, true otherwise
     */
    public function setRouter($router_inst)
    {
        if (!is_a($router_inst, 'Pfw_Controller_Router_Base')) {
            throw new Pfw_Exception_Type();
        }
        $old_router = ($this->router_inst === null) ? true : $this->router_inst;
        $this->router_inst = $router_inst;
        return $old_Router;
    }


    public function setMapper($mapper){
        if (!is_a($mapper, 'Pfw_Controller_Mapper_Base')) {
            throw new Pfw_Exception_Dispatch("Mappers must be instances of ".
                "Pfw_Controller_Mapper_Base");
        }
        $this->cont_act_mapper = $mapper;
    }


    public function getMapper()
    {
        if (!isset($this->cont_act_mapper)) {
            Pfw_Loader::loadClass('Pfw_Controller_Mapper_Standard');
            $this->cont_act_mapper = new Pfw_Controller_Mapper_Standard();
        }
        return $this->cont_act_mapper;
    }


    public function getController()
    {
        if (isset($this->controller)) {
            return $this->controller;
        }
        return null;
    }

    /**
     * Routes the request through the router with assigned routes
     *
     * @param array $routes
     */
    public function dispatch()
    {
        Pfw_PluginManager::execPreRoute();
        $script_url = $_SERVER['REQUEST_URI'];
        
        if (false !== ($qpos = strpos($script_url, '?'))) {
        	$script_url = substr($script_url, 0, $qpos);
        }
        
        $route_params = $this->getRouter()->route($script_url);
        Pfw_Request::setParams($route_params);

        if (empty($route_params)) {
            Pfw_Loader::loadClass('Pfw_Exception_NoRoute');
            throw new Pfw_Exception_NoRoute("Failed to find matching route for url path: '{$script_url}'");
        }
        Pfw_PluginManager::execPostRoute();

        $module = isset($route_params['module']) ? $route_params['module'] : null;
        
        Pfw_PluginManager::execPreMap();
        $cont_action = $this->getMapper()->map(
            $route_params['controller'],
            $route_params['action'],
            $module
        );
        Pfw_PluginManager::execPostMap();
        
        Pfw_PluginManager::execPreDispatch();
        $controller = $cont_action['controller'];
        $method = $cont_action['method'];

        $controller->_setFrontController($this);
        $this->controller = $controller;
        $controller->$method();
        Pfw_PluginManager::execPostDispatch();
    }
}
