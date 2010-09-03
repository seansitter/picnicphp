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
class Pfw_Db_Expr
{
    private $value;

    public function __construct($value)
    {
       $this->setValue($value);
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    public function setAlias($alias)
    {
        $this->use_alias = $alias;
    }
    
    public function resetAlias()
    {
        unset($this->use_alias);
    }

    public function getValue($alias = null)
    {
        if (is_null($alias) and isset($this->use_alias)) {
            $alias = $this->use_alias;
        }

        if (!isset($alias)) {
            return preg_replace("/\{\*\s*([\w]*)?\s*\*\}/", "$1", $this->value);
        }
        
        return preg_replace("/\{\*\s*([\w]*)?\s*\*\}/", "`$alias`.`$1`", $this->value);
    }
    
    public function __toString()
    {
        return $this->getValue();
    }
}
