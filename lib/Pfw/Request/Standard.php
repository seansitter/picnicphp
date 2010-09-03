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
 * Standard request adapter provides convenience methods
 * to access to properties of the request
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Request_Standard {
    protected $method;
    protected $params;
    protected $headers;
    
    public function __construct()
    {
        //$this->params =& $_REQUEST;
        $this->params = array();
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->headers = getallheaders();
        
        // make all headers lowercase
        foreach ($this->headers as $header => $value) {
        	unset($this->headers[$header]);
        	$this->headers[strtolower($header)] = $value;
        }
    }
    
    public function getHttpMethod() 
    {
        return $this->method;
    }

    public function setParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }
    
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    public function getParams()
    {
        return array_merge($_POST, $_GET, $this->params);
    }
    
    public function getParam($name)
    {
        //objp($_REQUEST);
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        elseif (isset($_GET[$name])) {
            return $_GET[$name];
        }
        elseif (isset($_POST[$name])) {
            return $_POST[$name];
        }
        
        return null;
    }
    
    public function paramEmpty($name)
    {
    	return empty($this->params[$name]) and
            empty($_GET[$name]) and empty($_POST[$name]);
    }
    
    public function getEntityBody()
    {
        // do input stream stuff here
        return file_get_contents('php://input');
    }
    
    public function _setHttpMethod($method)
    {
        $this->method = strtoupper($method);
    }
    
    public function isHttpPost()
    {
        return ($this->getHttpMethod() == "POST") ? true : false;
    }
    
    public function isAjax()
    {
        return ($this->getHeaderParam('x-requested-with') == 'XMLHttpRequest') ? true : false;
    }
    
    public function isRawHttpPost()
    {
        $param_name = 'Content-Type';
        $param_value = 'application/x-www-form-urlencoded';
        return ($this->isHttpPost() and ($this->getHeaderParam($param_name) != $param_value));
    }
    
    public function isHttpGet()
    {
        return ($this->getHttpMethod() == "GET") ? true : false;
    }
    
    public function isHttpPut()
    {
        return ($this->getHttpMethod() == "PUT") ? true : false;
    }
    
    public function isRawHttpPut()
    {
        $param_name = 'Content-Type';
        $param_value = 'application/x-www-form-urlencoded';
        return ($this->isHttpPut() and ($this->getHeaderParam($param_name) != $param_value));
    }
    
    public function isHttpDelete()
    {
        return ($this->getHttpMethod() == "DELETE") ? true : false;
    }
    
    public function isHttpHead()
    {
        return ($this->getHttpMethod() == "HEAD") ? true : false;
    }
    
    public function isHttpOption()
    {
        return ($this->getHttpMethod() == "OPTIONS") ? true : false;
    }
    
    public function getHeaderParam($param) 
    {
    	$param = strtolower($param);
        if (isset($this->headers[$param])) {
            return $this->headers[$param];
        }
        return null;
    }
    
    public function headerParamEmpty($param)
    {
    	$param = strtolower($param);
        return isset($this->headers[$param]) ? false : true;
    }
    
    public function getAllHeaders()
    {
    	return $this->headers;
    }
}
