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

Pfw_Loader::loadClass('Pfw_Exception_Db');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Exception_Db_Mysqli extends Pfw_Exception_Db {
    public function __construct($message = null, $code = null)
    {
        switch($code) {
            case 1060:
            case 1061:
            case 1062:
                $code = Pfw_Exception_Db::CODE_DUPLICATE;
        }
        return parent::__construct($message, $code);
    }
}
