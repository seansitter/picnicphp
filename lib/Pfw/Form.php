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
 * A basic class to hold form contents. A validator may be instantiated
 * with an instance of this class in place of a Pfw_Model
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Form
{
    protected $_errors = null;
    
    public function __construct($properties = array()){
    	if (!empty($properties)) {
            $this->setProperties($properties);
    	}
    }
    
    public static function getFormSet($form_input, $class = null) {
        $form_set = array();
        if (is_null($class)) {
        	$class = __CLASS__;
        }
        if (!empty($form_input)) {
            foreach ($form_input as $key => $properties) {
        	   $form_set[$key] = new $class($properties);
            }
        }
        return $form_set;
    }
    
    /**
     * Determines if this class has $property, even if it is set to null.
     * 
     * @param string $property
     * @return bool
     */
    public function hasProperty($property) 
    {
        return array_key_exists($property, get_object_vars($this)) ? true : false;
    }
    
    /**
     * Assignment occurs only on properties
     * of this which are not already set
     *
     * @param array $properties property => value pairs
     */
    public function setProperties($properties)
    {
    	if (!is_array($properties)) {
    		$properties = array($properties);
    	}
        while (list($name, $value) = each($properties)) {
            if (!is_valid_prop($name)) {
                error_log("'$name' is an invalid property name when building form.");
                continue;
            } 
            // ignore it if it was already set
            if (!isset($this->$name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Throws a Pfw_Exception_NotImplemented. 
     * Must be implemented in a subclass.
     */
    public function validate()
    {
        Pfw_Loader::loadClass('Pfw_Exception_NotImplemented');
        throw new Pfw_Exception_NotImplemented(
            "you must subclass Pfw_Form and implement the validate method"
        );
    }
    

    /**
     * Does this instance have validation errors?
     *
     * @param string $field if null, check specific field, else
     * check all fields
     */
    public function hasErrors($field = null)
    {
        if (is_null($this->_errors)) {
            return false;
        }
        if (!is_null($field)) {
            return empty($this->_errors[$field]) ? false : true;
        }
        
        return empty($this->_errors) ? false : true;
    }


    /**
     * Add an error to a field with message
     *
     * @param string $field
     * @param string $message
     */
    public function addError($field, $message)
    {
        if (is_null($this->_errors)) {
            $this->errors = array();
        }
        if (false !== strpos($message, '%s')) {
            $message = sprintf($message, $this->$field);
        }
        $this->_errors[$field][] = $message;
    }


    /**
     * Get the validation errors for this instance
     *
     * @param string $field if null, get specific field errors, else
     * get errors on all fields
     */
    public function getErrors($field = null, $collapse = true)
    {
        if (is_null($this->_errors)) {
            return array();
        }
        if(!is_null($field)){
            return $this->_errors[$field];
        }
        if (true == $collapse) {
            $all_errors = array();
            foreach ($this->_errors as $field => $errors) {
                if(!is_array($errors)) {$errors = array($errors);}
                $all_errors = array_merge($all_errors, $errors);
            }
            return $all_errors;
        }
        return $this->_errors;
    }
    
    
    /**
    * Clear the validation errors for this instance
    *
    * @param string $field if null, clear specific field errors, else
    * clear errors on all fields
     */
    public function clearErrors($field = null)
    {
        if (!is_null($field)) {
            $this->_errors[$field] = array();
        }
        else {
            $this->_errors = array();
        }
    } 
}
