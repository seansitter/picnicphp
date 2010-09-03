<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @category      Framework
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

require("Pfw/Loader.php");
Pfw_Loader::loadInclude("Pfw_Core_Base");
Pfw_Loader::loadClass("Pfw_Config");
Pfw_Loader::loadClass('Pfw_Exception_User');
Pfw_Loader::loadClass('Pfw_Exception_System');
Pfw_Loader::loadClass('Pfw_Model');
Pfw_Loader::loadClass('Pfw_Cache_Local');
Pfw_Loader::loadClass('Pfw_Request');
