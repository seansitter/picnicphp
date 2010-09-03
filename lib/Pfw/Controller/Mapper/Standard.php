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

Pfw_Loader::loadClass('Pfw_Controller_Mapper_Base');
Pfw_Loader::loadClass('Pfw_Exception_Dispatch');

/**
 * Default implementation of controller mapper. Maps a route to a 
 * Controller class and Action method.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Controller_Mapper_Standard extends Pfw_Controller_Mapper_Base
{
    const ACTION_SUFFIX = 'Action';

    private $valid_seps = array('-','_','.');
    // yes, yes, could be a regex, but this should be faster
    private $alpha = array('a','b','c','d','e','f','g','h','i','j','k',
        'l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    private $numeric = array('0','1','2','3','4','5','6','7','8','9');

    protected $map = array();

    /**
     * Maps controller and action names to a concrete class and action,
     * which are the result of routing
     * 
     * @param string $controller name of the controller
     * @param string $action name of the action
     * @param string $module name of the module
     * @return array contains to keys, 'controller' and 'action' which are
     * the names of the controller class and action method, respectively
     */
    public function map($controller, $action, $module = null)
    {
        if (empty($controller) or empty($action)) {
            return false;
        }
        
        $controller_key = "_pfw_map_";
        if (!empty($module)) {
            $controller_key .= $module."_";
        }
        $controller_key .= $controller;
        
        if (false !== ($controller_class = Pfw_Cache_Local::get($controller_key))) {
            Pfw_Loader::loadController($controller_class);
            $key = '_pfw_map_'.$controller.'::'.$action;
            if (false !== ($this->map = Pfw_Cache_Local::get($key))) {
                $this->map['controller'] = new $controller_class();
                return $this->map;
            }
        }
        
        $controller_class = $this->mapController($module, $controller);
        if (empty($controller_class)) {
            throw new Pfw_Exception_NotFound(
                "Cound not find controller class for '$controller'",
                Pfw_Exception_NotFound::ROUTE_MISSING_CONTROLLER
            );
        }

        $this->map = $this->mapAction($controller_class, $action);
        if (empty($this->map)) {
            throw new Pfw_Exception_NotFound("Could not find method for '$action' within class '$controller_class'. ".
                "Please ensure the method '{$action}Action()' exists.");
        }
        
        $c_map = $this->map;
        unset($c_map['controller']);
        
        Pfw_Cache_Local::set($key, $c_map);
        Pfw_Cache_Local::set($controller_key, $controller_class);

        return $this->map;
    }
    

    /**
     * Returns the an array with two keys: 'controller' and 'action', 
     * the name of the controller class and action method, respectively
     * 
     * @return array the controller / action map
     */
    public function getMap()
    {
        return $this->map;
    }


    protected function mapController($module, $controller)
    {
        if (!$this->isAlNum($controller[0])) {
            throw new Pfw_Exception_Dispatch("Controller name must start ".
                "with an alphanumeric character");
        }

        $len = strlen($controller);
        if($this->isAlpha($controller[0])){
            $class = strtolower($controller[0]);
        }

        for ($i = 1; $i < $len; $i++) {
            $c = $controller[$i];
            if ($this->isSep($c)) {
                if (isset($controller[$i+1])) {
                    if ($this->isAlpha($controller[$i+1])) {
                        $class .= strtoupper($controller[$i+1]);
                        $i+=1;
                    }
                }
            } else {
                if (!$this->isAlNum($c)) {
                    throw new Pfw_Exception_Dispatch("Non alphanumeric character '$c' ".
                        "found in controller name: $controller.");
                }
                $class .= strtolower($c);
            }
        }

        $class = ucfirst($class).'Controller';

        if (!is_null($module)) {
            $module = ucfirst(strtolower($module));
            $class = "{$module}_{$class}";
        }

        return $class;
    }

    protected function mapAction($controller_class, $action)
    {
        try {
            Pfw_Loader::loadController($controller_class);
        } catch(Pfw_Exception_Loader $e) {
            throw new Pfw_Exception_NotFound(
                "Could not find controller class '$controller_class'. ".
                "Please ensure class '{$controller_class}.php' exists within the 'controllers' directory",
                Pfw_Exception_NotFound::ROUTE_MISSING_ACTION
            );
        }
        $inst = new $controller_class();

        $methods_key = '_pfw_'.strtolower($controller_class).'_methods';
        if(false === ($methods = Pfw_Cache_Local::get($methods_key))) {
            $methods = get_class_methods($inst);
            Pfw_Cache_Local::set($methods_key, $methods);
        }
        $methods = get_class_methods($inst);
        $n_action = $this->normalizeAct($action);

        foreach ($methods as $method) {
            if (!$this->isAlpha($method[0])){
                continue;
            }
            if (self::ACTION_SUFFIX == substr($method, -6)) {
                $act_part = substr($method, 0, strlen($method) - 6);
                if ($n_action == $this->normalizeAct($act_part)) {
                    return array(
                        'controller' => $inst,
                        'controller_class' => $controller_class,
                        'method' => $method
                    );
                }
            }
        }
        return array();
    }

    private function normalizeAct($action){
        if(empty($action)){
            return "";
        }

        $len = strlen($action);
        $n_act = "";
        $found_first_alpha = false;

        for ($i = 0; $i < $len; $i++) {
            $c = $action[$i];
            if (!$this->isAlpha($c)){
                if ($this->isNumeric($c) and $found_first_alpha) {
                    $n_act .= $c;
                } elseif (!$this->isNumeric($c) and !$this->isSep($c)) {
                    throw new Pfw_Exception_Dispatch("Non alphanumeric character '$c' ".
                        "found in action name: $action.");
                }
            } else {
                $n_act .= strtolower($c);
                $found_first_alpha = true;
            }
        }
        return $n_act;
    }

    private function isAlNum($char)
    {
        return $this->isAlpha($char) or
        $this->isNumeric($char);
    }

    private function isAlpha($char)
    {
        return in_array(strtolower($char), $this->alpha);
    }

    private function isNumeric($char)
    {
        return in_array($char, $this->numeric);
    }

    private function isSep($char){
        return in_array($char, $this->valid_seps);
    }
}
