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

Pfw_Loader::loadClass('Prj_Smarty_Standard');

/**
 * This class is default parent class of all controllers in your project.
 * It implements a number of convenience methods which simplify basic
 * tasks.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Controller_Standard
{
    protected $front_controller=null;
    protected $smarty = null;
    protected $config = null;
    protected $params = array();

    public function __construct()
    {
        $this->setupView();
    }

    protected function setupView(){ }

    /**
     * Gets an instance of the view object.
     *
     * @return Pfw_Smarty_Standard
     */
    public function getView()
    {
        if (null === $this->smarty) {
            $this->smarty = new Prj_Smarty_Standard();
        }
        return $this->smarty;
    }

    /**
     * Returns true if request was an http post.
     *
     * @return bool  Request was a post?
     */
    public function isPost()
    {
        return Pfw_Request::isHttpPost();
    }

    /**
     * Returns true if request was an http get.
     *
     * @return bool  Request was a get?
     */
    public function isGet()
    {
        return Pfw_Request::isHttpGet();
    }
    
    /**
     * Gets a variable from a the request environment, for example
     * from a matching :name route segment, or by index from a route
     * wildcard.
     * 
     * @param string $name the route 
     * @return string the value of the request environment variable
     */
    public function getParam($name)
    {
        return Pfw_Request::getParam($name);
    }
    
    /**
     * Is the request parameter empty?
     * 
     * @param string $name
     * @return bool
     */
    public function paramEmpty($name)
    {
        return Pfw_Request::paramEmpty($name);
    }

    /**
     * Gets the name => value pair array of request parameters.
     * 
     * @return array
     */
    public function getParams()
    {
        return Pfw_Request::getParams();
    }

    /**
     * Returns true if request was ajax.
     * NOTE - Only works if the javascript library sends the X_REQUESTED_WITH
     * header.
     *
     * @return bool
     */
    public function isAjax()
    {
        return Pfw_Request::isAjax();
    }

    /**
     * Gets the url for a specific route name and route params.
     * 
     * @param array $route_params controller, action, etc
     * @param string $route_name
     * @return string
     */
    public function urlFor($route_params = array(), $route_name = "default_action")
    {
        return $this->_getFrontController()->getRouter()->urlFor($route_name, $route_params);
    }

    /**
     * Sets the instance of the front controller which routed our request.
     *
     * @param Pfw_Controller_Front $front_controller
     */
    public function _setFrontController($front_controller)
    {
        $this->front_controller = $front_controller;
    }

    /**
     * Gets the instance of the front controller which routed our request.
     *
     * @return Pfw_Controller_Front
     */
    public function _getFrontController()
    {
        return $this->front_controller;
    }
    
    /**
     * Redirect to a specific route name and route params.
     * 
     * @param array $route_params controller, action, etc
     * @param string $route_name
     */
    public function redirectTo($route_params = array(), $route_name = "default_action")
    {
        $this->redirectRelative($this->urlFor($route_params, $route_name));
    }

    /**
     * Redirects to a relative url with the same http host.
     *
     * @param string $rel_url
     * @param string $qs
     * @param string $anchor
     */
    public function redirectRelative($rel_url, $qs = null, $anchor = null)
    {
        $qs_str = "";
        if($qs){
            foreach($qs as $key => $val){
                if($val){
                    $val = (string)$val;
                    $val = urlencode($val);
                    $qs_str .= "{$key}={$val}&";
                }
                else{
                    $qs_str .= $key;
                }
            }
            $qs_str = '?' . $qs_str;
        }
        $qs_str = rtrim($qs_str, '&');
        $anchor = $anchor ? "#{$anchor}" : '';

        $destination = "http://{$_SERVER['HTTP_HOST']}{$rel_url}{$qs_str}{$anchor}";
        $this->redirect($destination);
        exit();
    }

    /**
     * Redirect to the destination url.
     *
     * @param string $destination
     */
    public function redirect($url)
    {
        header('Location: '.$url);
    }
}
