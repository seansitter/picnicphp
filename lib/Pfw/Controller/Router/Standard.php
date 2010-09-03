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

Pfw_Loader::loadClass('Pfw_Controller_Router_Base');

/**
 * Given a set of routes and a url path, attempts to path to route &
 * decomposes path into a set of variables defined in the route
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Controller_Router_Standard extends Pfw_Controller_Router_Base
{
    protected $route = array();

    /**
     * Gets the conditions portion of a given route
     *
     * @param array $route  the route
     * @return array  the conditions portion of the given route
     */
    protected function _getRouteConditions($route_name, $route)
    {
        if (!isset($route[2])) {
            $route_conds = array();
        } elseif (!is_array($route[2])) {
            throw new Pfw_Exception_Route("Route conditions must be expressed ".
                "as an array in route: {$route_name}");
        } else {
            $route_conds = $route[2];
        }
        return $route_conds;
    }


    /**
     * Gets the defaults portion of a given route
     *
     * @param array $route  the route
     * @return array  the defaults portion of the given route
     */
    protected function _getRouteDefaults($route_name, $route)
    {
        if (!isset($route[1])) {
            $route_def = array();
        } elseif (!is_array($route[1])) {
            throw new Pfw_Exception_Route("Route defaults must be expressed ".
               "as an array in route: {$route_name}");
        } else {
            $route_def = $route[1];
        }
        return $route_def;
    }


    /**
     * Tests conditions of a path segment against route constraints
     *
     * @param string $rt_seg_value  the value of the path segment
     * @param array $seg_conds      the conditions to apply
     */
    protected function _applySegConds($rt_seg_value, $seg_conds)
    {
        $len = count($seg_conds);
        for ($i = 0; $i < $len; $i++) {
            if (is_a($seg_conds[$i], 'Pfw_Controller_Route_SegmentCondition')) {
                if(true === $seg_conds[$i]->exec($rt_seg_value)) {
                    return true;
                }
            } elseif(self::isRegexp($seg_conds[$i])) {
                if (true === self::matchRegexp($seg_conds[$i], $rt_seg_value)) {
                    return true;
                }
            } elseif(is_string($seg_conds[$i])) {
                if ($seg_conds[$i] == $rt_seg_value) {
                    return true;
                }
            } else {
                trigger_error("Unknown segment condition type: {$seg_conds[$i]}",
                E_USER_WARNING);
            }
        }

        // we failed to match conditions
        return false;
    }


    /**
     * Matches a given path to a compiled route path.
     * Returns false on failure.
     * Returns 0 on perfect match
     * Returns > 0 on a match which could potentially be completed with defaults
     * Applies conditions
     *
     * @param array $path             The path to match
     * @param array $compiled_path    The compiled route
     * @param array $conds            The conditions array
     * @param array $result           The result array
     * @return bool, int              False onfailure, true on success,
     *                                >= 0 otherwise if could match w/wildcard
     *                                or default fill-ins
     */
    protected function _matchRoute($path, $compiled_path, $conds, &$result)
    {
        $path_len = count($path);
        $success_counter = 0;

        for ($i = 0; $i < $path_len; $i++) {
            if (!isset($compiled_path[$i])) {
                // if compiled route is shorter, we fail because this would
                // require a wildcard which we would have seen and ret already
                return false;
            }

            $cr_seg_type = $compiled_path[$i][0];
            $cr_seg_value = $compiled_path[$i][1];
            $cr_len = count($compiled_path);
            $rt_seg_value = $path[$i];

            if ($cr_seg_type == self::ROUTE_PART_LIT) {
                // match literal pieces
                if ($cr_seg_value != $path[$i]) {
                    $result = array();
                    return false;
                }
                $success_counter++;
                continue;
            } elseif ($cr_seg_type == self::ROUTE_PART_WILD) {
                // match wildcard pieces
                if($i != ($cr_len - 1)){
                    throw new Pfw_Exception_Route("Route wildcards must " .
                        "appear at the end of the route path.");
                }
                // stuff the remainder into $result
                for ($k = $i, $argidx = 0; $k < $path_len; $k++, $argidx++) {
                    $result[$argidx] = $path[$k];
                }
                // we're automatically successful
                return true;
            } elseif ($cr_seg_type == self::ROUTE_PART_VAR) {
                // match variable pieces

                if ("module" == $cr_seg_value) {
                    if (!$this->isModule($rt_seg_value)) {
                        return false;
                    }
                }

                /* if the route conditions for this named route variable part
                 * aren't set, this named route part value is assigned to its
                 * variable name
                 */
                if (empty($conds[$cr_seg_value])) {
                    $result[$cr_seg_value] = $path[$i];
                    $success_counter++;
                    continue;
                }

                $seg_conds = $conds[$cr_seg_value];
                if (!is_array($seg_conds)) {
                    $seg_conds = array($seg_conds);
                }

                if (!$this->_applySegConds($rt_seg_value, $seg_conds)) {
                    $result = array();
                    return false;
                }

                // success!
                $result[$cr_seg_value] = $path[$i];
                $success_counter++;
                continue;
            } else {
                return false;
            }
        }

        // handle perfect case and compiled route is longer case
        $compiled_path_len = count($compiled_path);
        if (($success_counter == $compiled_path_len) and
        ($success_counter == $path_len)) {
            // perfect match!
            return true;
        }

        // compiled route is longer, throw this back
        return $success_counter;
    }


    /**
     * Attempts to match remaining segments from compiled route to path when
     * compiled route is longer than path
     *
     * @param array $compiled_path    The remaining compiled path that we have to match to 
     *                                defaults
     * @param array $route_defaults   The route defaults to match against
     * @param array $result           The result
     * @return bool                   Fail or succeed?
     */
    protected function _matchRemDefaults($compiled_path, $route_defaults, &$result)
    {
        if (isset($_GET['action'])) {
            $route_defaults['action'] = $_GET['action'];
        }
        
        $compiled_path_len = count($compiled_path);
        for ($i = 0; $i < $compiled_path_len; $i++) {
            $segment = $compiled_path[$i];
            if ($segment[0] == self::ROUTE_PART_LIT) {
                return false;
            } elseif($segment[0] == self::ROUTE_PART_WILD) {
                if ($i == ($compiled_path_len - 1)) {
                    // we reached the end and found a wild card, its a given
                    // we don't have any remainders
                    return true;
                } else {
                    // we didn't reach the end but found a wild card, malformed
                    trigger_error(
                        "A wildcard has no place in the middle of a ".
                        "route!", E_USER_WARNING
                    );
                    return false;
                }
            } elseif ($segment[0] == self::ROUTE_PART_VAR) {
                // if we can fill it, we will, otherwise we fail
                if (array_key_exists($segment[1], $route_defaults)) {
                    $result[$segment[1]] = $route_defaults[$segment[1]];
                } else {
                    return false;
                }
            } else {
                // some unknown segment type
                return false;
            }
        }
        return true;
    }


    /**
     * Attempts to backfill defaults missing from final result
     *
     * @param array $route_defaults  The route defaults
     * @param array $result          The almost-complete route result
     */
    function _backfillDefaults($route_defaults, &$result) {
        // foreach default variable, make sure its represented in the result
        foreach($route_defaults as $key => $value) {
            if (!isset($result[$key])) {
                $result[$key] = $value;
            }
        }
    }

    /**
     * Compiles all route paths into component pieces of literals, variables,
     * wildcards
     *
     * @param array $routes
     * @return array
     */
    protected function _compileRoutePaths($routes)
    {
    	$cache_key = md5('_pfw_route_paths');
    	if (false == ($comp = Pfw_Cache_Local::get($cache_key))) {
            $comp = array();
            foreach ($routes as $name => $params) {
                $comp[$name] = $this->_compileRoutePath($params[0]);
            }
            Pfw_Cache_Local::set($cache_key, $comp);
    	}
        return $comp;
    }


    /**
     * Matches the script path to a configured route
     *
     * @param string $path   the full url following the hostname with first '/'
     * @return array         an array of name => value pairs from the
     *                       decomposed url
     */
    public function route($path)
    {
        $path = $this->splitPath($path);
        //TODO - this should go to apc
        $compiled_paths = $this->_compileRoutePaths($this->routes);
        $result = null;

        foreach ($this->routes as $name => $route) {
            $result = array();
            $success_route_name = null;
            // get the compiled route path for this route name
            $route_compiled_path = $compiled_paths[$name];
            // get the defaults for the segment values
            $route_defaults = $this->_getRouteDefaults($name, $route);
            // get the conditions for all segments
            $route_conds = $this->_getRouteConditions($name, $route);

            // get the best possible match on this route
            $diff = $this->_matchRoute(
                $path,
                $route_compiled_path,
                $route_conds,
                $result
            );
            
            
            if (false === $diff) {
                // this route failed
                continue;
            } elseif (true === $diff) {
                // route was an exact match
                $success_route_name = $name;
                break;
            } elseif ($diff >= 0) {
                // path was shorter than compiled route, try to match w/defaults
                $rem_comp_route = array_slice($route_compiled_path, $diff);
                $res = $this->_matchRemDefaults(
                    $rem_comp_route,
                    $route_defaults,
                    $result
                );
                if(true == $res){
                    $success_route_name = $name;
                    break;
                }
            } else {
                // this route failed for unknown reason
                continue;
            }
        }

        // we found a matching route, finish up!
        if (null !== $success_route_name) {
            $route = $this->routes[$success_route_name];
            $route_defaults = $this->_getRouteDefaults($success_route_name, $route);
            $this->_backfillDefaults($route_defaults, $result);
            $this->setSuccessRouteName($success_route_name);
            $this->route = $result;
        }
        
        return $this->route;
    }


    public function getRoute()
    {
        return $this->route;
    }


    public function urlFor($route_name, $route_params = array())
    {
        $url = "";
        $default_params = isset($this->routes[$route_name][1]) ?
            $this->routes[$route_name][1] : array();
        $route_pieces = $this->_compileRoutePath($this->routes[$route_name][0]);
        $path_len = count($route_pieces);

        for ($i = 0; $i < $path_len; $i++) {
            if ($route_pieces[$i][0] == self::ROUTE_PART_LIT) {
                $part = $route_pieces[$i][1];
                $url = $url.'/'.$part;
            } elseif ($route_pieces[$i][0] == self::ROUTE_PART_VAR) {
                $var_name = $route_pieces[$i][1];
                if (array_key_exists($var_name, $route_params)) {
                    $part = $route_params[$var_name];
                    unset($default_params[$var_name]);
                    unset($route_params[$var_name]);
                } elseif(array_key_exists($var_name, $default_params)) {
                    $part = $default_params[$var_name];
                    unset($default_params[$var_name]);
                    unset($route_params[$var_name]);
                } else {
                    $part = "/";
                }
                $url = $url.'/'.$part;
            } elseif ($route_pieces[$i][0] == self::ROUTE_PART_WILD) {
                # we'll ignore this and make everything else part of qs
            }
        }
        
        $qs_parts = array_merge($default_params, $route_params);
        if (isset($qs_parts['action'])) {
            unset($qs_parts['action']);
        }
        if (isset($qs_parts['controller'])) {
            unset($qs_parts['controller']);
        }
        if (isset($qs_parts['module'])) {
            unset($qs_parts['module']);
        }

        $qs="";
        $keys = array_keys($qs_parts);
        foreach ($keys as $key) {
            if (gettype($key) == 'integer'){
                $piece = urlencode($qs_parts[$key])."=1";
            } else {
                $piece = urlencode($key)."=".urlencode($qs_parts[$key]);
            }
            $qs .= "{$piece}&";
        }
        $qs = rtrim($qs, '&');

        if(strlen($qs) > 0){
            $url .= "?".$qs;
        }

        return $url;
    }


    /**
     * Compiles a given route path
     *
     * @param string $path
     */
    protected function _compileRoutePath($path)
    {
        $tok = strtok($path, '/');
        $compiled_path = array();

        while ($tok !== false) {
            if ($tok[0] == ":") {
                $var_name = substr($tok, 1);
                array_push($compiled_path, array(self::ROUTE_PART_VAR, $var_name));
            } else {
                array_push($compiled_path, array(self::ROUTE_PART_LIT, $tok));
            }
            $tok = strtok('/');
        }

        // check if last part is wildcard
        // TODO - throw exception if wildcard is not last
        $lastid = count($compiled_path) - 1;
        if ($compiled_path[$lastid][1] == '*') {
            $compiled_path[$lastid][0] = self::ROUTE_PART_WILD;
        }

        return $compiled_path;
    }

    protected static function isRegexp($str)
    {
        if (($str[0] == '/') and (substr($str, -1, 1) == '/')) {
            return true;
        }
        return false;
    }

    protected static function matchRegexp($regexp, $str)
    {
        $out = preg_match($regexp, $str);
        if ($out === false) {
            trigger_error("Regular expression condition $regexp failed with " .
                "an error. Make sure $regexp is a properly formed regular " .
                "expression.", E_USER_ERROR);
        }
        return ($out > 0) ? true : false;
    }
}
