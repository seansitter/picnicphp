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

$path = dirname(dirname(dirname(dirname(__FILE__))));
set_include_path($path.PATH_SEPARATOR.get_include_path());
require_once('Pfw/Startup/Base.php');
Pfw_Loader::loadClass('Pfw_UnitTest_PHPUnit_TestCase');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_UnitTest_PHPUnit_FwkTestCase extends Pfw_UnitTest_PHPUnit_TestCase
{
    public function __construct()
    {
        parent::__construct();

        global $_PATHS, $_ENVIRONMENT;
        $_PATHS['conf'] = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."test".DIRECTORY_SEPARATOR."conf";
        $_ENVIRONMENT = isset($_ENVIRONMENT) ? $_ENVIRONMENT : "test";
        Pfw_Config::reset();
        Pfw_Config::init($_ENVIRONMENT);
    }
}
