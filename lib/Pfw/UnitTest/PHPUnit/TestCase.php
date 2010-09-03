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

require_once('Pfw/Startup/Base.php');
if (false == @(include_once 'PHPUnit/Framework.php')) {
   error_log(
       "\nPHPUnit does not appear to be installed. Please visit:\n".
       "http://www.phpunit.de/manual/current/en/installation.html\n"
   );
   exit(); 
}

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_UnitTest_PHPUnit_TestCase extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function uniqid()
    {
        return uniqid("test_", true);
    }

    public function assertExceptionOnFunction($function, $args, $exception_name, $code = null)
    {
        try {
            call_user_func_array($function, $args);
            $this->assertTrue(false, "Should never get here, '$exception_name' should have been thrown.");
        } catch (Exception $e) {
            $this->assertTrue(is_a($e, $exception_name));
            if (!is_null($code)) {
                $this->assertEquals($code, $e->getCode());
            }
        }
    }
}
