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
 * Provides access to the request environmen, including route
 * and query string variables, the http header, and the request body.
 *
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Request
{
    protected static $adapter = null;
    
    /**
     * Initialize the request. If adapter is null,
     * Pfw_Request_Standard will be used.
     * 
     * @param string $adapter the adapter class
     */
    public static function init($adapter = null) {
        if (!is_null($adapter)) {
            self::$adapter = $adapter;
        } else {
            $adapter_class = "Pfw_Request_Standard";
            Pfw_Loader::loadClass($adapter_class);
            self::$adapter = new $adapter_class();
        }
    }
    
    /**
     * Gets the request adaper currently in use.
     * 
     * @return Pfw_Request subclass of Pfw_Request.
     */
    public static function getAdapter() {
        if (is_null(self::$adapter)) {
            self::init();
        }

        return self::$adapter;
    }
    
    /**
     * Merges $params into the existing request params.
     * 
     * @param array $params
     */
    public static function setParams($params)
    {
        return self::getAdapter()->setParams($params);
    }
    
    /**
     * Sets a request param.
     * 
     * @param string $name
     * @param string $value
     */
    public static function setParam($name, $value)
    {
        self::getAdapter()->setParam($name, $value);
    }
    
    /**
     * Gets the entire array of request params.
     * 
     * @return array the request params
     */
    public static function getParams()
    {
        return self::getAdapter()->getParams();
    }
    
    /**
     * Gets a variable from a the request environment, for example
     * from a matching :name route segment, or by index from a route
     * wildcard.
     * 
     * @param string $name the route 
     * @return string the value of the request environment variable
     */
    public static function getParam($name)
    {
        return self::getAdapter()->getParam($name);
    }
    
    /**
     * Is the request parameter empty?
     * 
     * @param string $name
     * @return bool
     */
    public static function paramEmpty($name)
    {
        return self::getAdapter()->paramEmpty($name);
    }
    
    /**
     * Override the request http method. 
     * 
     * @param string $method a valid http method
     */
    public static function _setHttpMethod($method)
    {
        self::getAdapter()->_setHttpMethod($method);
    }
    
    /**
     * Is the request an ajax request? Checks if the
     * http header 'x-requested-with' == 'XMLHttpRequest'
     * 
     * @return bool
     */
    public static function isAjax()
    {
        return self::getAdapter()->isAjax();
    }
    
    /**
     * Is the request an http post? Checks if the http 
     * request method is 'POST'
     * 
     * @return bool
     */
    public static function isHttpPost()
    {
        return self::getAdapter()->isHttpPost();
    }
    
    /**
     * Is the request a raw http post? Checks if the http 
     * request method is 'POST' and that the http header 
     * 'Content-Type' != 'application/x-www-form-urlencoded' 
     * ie: the post body is a blob.
     * 
     * @return bool
     */
    public static function isRawHttpPost()
    {
        return self::getAdapter()->isRawHttpPost();
    }
    
    /**
     * Is the request an http get? Checks if the 
     * request method is 'GET'
     * 
     * @return bool
     */
    public function isHttpGet()
    {
        return self::getAdapter()->isHttpGet();
    }
    
    /**
     * Is the request an http put? Checks if the http 
     * request method is 'PUT'
     * 
     * @return bool
     */
    public function isHttpPut()
    {
        return self::getAdapter()->isHttpPut();
    }
    
    /**
     * Is the request an http put? Checks if the http 
     * request method is 'PUT' and that the http header 
     * 'Content-Type' != 'application/x-www-form-urlencoded' 
     * ie: the put body is a blob.
     * 
     * @return bool
     */
    public static function isRawHttpPut()
    {
        return self::getAdapter()->isRawHttpPut();
    }
    
    /**
     * Is the request and http delete? Checks if the http 
     * request method is 'DELETE'.
     * 
     * @return bool
     */
    public function isHttpDelete()
    {
        return self::getAdapter()->isHttpDelete();
    }
    
    /**
     * Is the request and http head? Checks if the http 
     * request method is 'HEAD'.
     * 
     * @return bool
     */
    public function isHttpHead()
    {
        return self::getAdapter()->isHttpHead();
    }
    
    /**
     * Is the request and http options? Checks if the http 
     * request method is 'OPTIONS'.
     * 
     * @return bool
     */
    public function isHttpOption()
    {
        return self::getAdapter()->isHttpOption();
    }

    /**
     * Gets the value of an http header.
     * 
     * @param string $param the header name
     * @return string
     */
    public static function getHeaderParam($param)
    {
        return self::getAdapter()->getHeaderParam($param);
    }
    
    /**
     * Is the request parameter empty?
     * 
     * @param string $name
     * @return bool
     */
    public static function headerParamEmpty($name)
    {
        return self::getAdapter()->headerParamEmpty($name);
    }
    
    /**
     * Returns all headers as an array, with the keys lowercased
     * 
     * @return array key => value header pairs
     */
    public static function getAllHeaders()
    {
    	return self::getAdapter()->getAllHeaders();
    }
    
    /**
     * Returns the http request method.
     * 
     * @return string
     */
    public static function getHttpMethod()
    {
        return self::getAdapter()->getHttpMethod();
    }
    
    /**
     * Gets the request entity body.
     * 
     * @return string
     */
    public static function getEntityBody()
    {
        return self::getAdapter()->getEntityBody();
    }
}
