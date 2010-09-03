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

Pfw_Loader::loadClass('Pfw_Exception_System');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Exception_Loader extends Pfw_Exception_System {
    const FILE_MISSING   = 1;
    const CLASS_MISSING  = 2;
    const FILENAME_EMPTY = 3;
    
    public function __construct($file, $code, $inc_path = null){
        switch ($code)
        {
            case self::FILE_MISSING:
                $message = "File \"{$file}\" could not be found.";
                break;
            case self::CLASS_MISSING:
                $message = "Class \"{$file}\" could not be found.";
                break;
            case self::FILENAME_EMPTY:
                $message = "Attempted to include empty filename.";
                break;
            default:
                $message = "File \"{$file}.php\" could not be found ".
                    "\"$file\" was not found in the file.";
        }
        
        if(null !== $inc_path){
            $message .= " Using include path: ".$inc_path;
        }
        
        return parent::__construct($message, $code);
    }
}
