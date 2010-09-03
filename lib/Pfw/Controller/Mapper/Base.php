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
 * Default implementation of controller mapper. Maps a route to a 
 * Controller class and Action method.
 * 
 * @category      Framework
 * @package       Pfw
 */
abstract class Pfw_Controller_Mapper_Base {
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
    abstract public function map($controller, $action, $module = null);
    
    /**
     * Returns the an array with two keys: 'controller' and 'action', 
     * the name of the controller class and action method, respectively
     * 
     * @return array the controller / action map
     */
    abstract public function getMap();
}
