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
class Pfw_Validate 
{
    protected $_failed = false;
    
    public function __construct($object)
    {
        $this->object = $object;
        $this->object_props = get_object_vars($object); 
    }
    
    public function success()
    {
        return (true === $this->_failed) ? false : true;
    }
    
    public function fail()
    {
        $this->_failed = true;
    }
    
    protected function addError($property, $message)
    {
        $this->_failed = true;
        if (false !== strpos($message, '%s')) {
            $message = sprintf($message, $this->object->$property);
        }
        $this->object->addError($property, $message);
        return false;
    }
    
    protected function hasProperty($property)
    {
        return array_key_exists($property, $this->object_props) ? true : false;
    }
    
    protected function emptyProperty($property)
    {
        $value = $this->getProperty($property);
        return empty($value) ? true : false;
    }
    
    protected function presentAndNotEmpty($property)
    {
        return $this->hasProperty($property) and !$this->emptyProperty($property);
    }
    
    protected function getProperty($property)
    {
        return $this->object->$property;
    }
    
    public function confirmation($property, $message="")
    {
        if ($this->presentAndNotEmpty($property)) {
            if ($this->getProperty($property.'_confirm')  == $this->getProperty($property)) {
                return true;
            }
            return $this->addError($property, $message);
        }
        return true;
    }
    
    public function presence($property, $message="")
    {
        if (!$this->hasProperty($property)) {
            return $this->addError($property, $message);
        }
        $value = $this->getProperty($property);
        if (!preg_match('/[0]/', $value) and (false !== $value) and empty($value)) {
            return $this->addError($property, $message);
        }
        return true;
    }
    
    public function format($property, $pattern, $message="")
    {
        if ($this->presentAndNotEmpty($property)) {
            if (false !== eregi($pattern, $this->getProperty($property))) {
                return true;
            }
            return $this->addError($property, $message);
        }
        return true;
    }

    public function formatNot($property, $pattern, $message="")
    {
        if ($this->presentAndNotEmpty($property)) {
            if (preg_match($pattern, $this->getProperty($property))) {
                return $this->addError($property, $message);
            }
            return true;
        }
        return true;
    }
    
    public function email($property, $message="")
    {
        // the email regular expression
        if ($this->presentAndNotEmpty($property)) {
            $pattern = '^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$';
            if (false !== eregi($pattern, $this->getProperty($property))) {
                return true;
            }
            return $this->addError($property, $message);
        }
        return true;
    }
    
    public function numeric($property, $message="")
    {
        if ($this->hasProperty($property)) {
            if (is_numeric($this->getProperty($property))) {
                return true;
            }
            return $this->addError($property, $message);
        }
        return true;
    }
    
    public function integer($property, $message="")
    {
        if ($this->hasProperty($property)) {
            if (preg_match('/[0-9]+/', $this->getProperty($property))) {
                return true;
            }
            return $this->addError($property, $message);
        }
        return true;
    }
}
