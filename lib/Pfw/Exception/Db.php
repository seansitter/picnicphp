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
class Pfw_Exception_Db extends Pfw_Exception_System {
    const CODE_UNSPECIFIED = 0;
    const CODE_UNKNOWN     = 1;
    const CODE_DUPLICATE   = 100;
    
    public function __construct($message = null, $code = null)
    {
        $code = is_null($code) ? self::CODE_UNSPECIFIED : $code;
        $message = is_null($message) ? "Unspecified error" : $message;
        
        return parent::__construct($message, $code);
    }
}
