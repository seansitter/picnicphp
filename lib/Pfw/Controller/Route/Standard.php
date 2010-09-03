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
class Pfw_Controller_Route_Standard
{
    /**
     * Compiles all route paths into component pieces of literals, variables,
     * wildcards
     *
     * @param array $routes
     * @return array
     */
    protected function _compileRoutePaths($routes)
    {
        $comp = array();

        foreach ($routes as $name => $params) {
            $comp[$name] = $this->_compileRoutePath($params[0]);
        }

        return $comp;
    }
}
