<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * PHP version 5
 *
 * Copyright 2008 The Picnic PHP Framework
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *            http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category            Framework
 * @package             Pfw
 * @author              Sean Sitter <sean@picnicphp.com>
 * @copyright           2008 The Picnic PHP Framework
 * @license             http://www.apache.org/licenses/LICENSE-2.0
 * @link                http://www.picnicphp.com
 * @since               0.10
 */

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');
Pfw_Loader::loadClass('Pfw_Script_CLI');

/**
 *
 * Uncomment tests to run them. Should not be run in harness since requires
 * user input
 *
 */
class Pfw_Script_CLI_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    /*
    function testPromptYesNoYes()
    {
        $this->assertTrue(Pfw_Script_CLI::promptYesNo("(type 'Y' <return>)"));
        $this->assertTrue(Pfw_Script_CLI::promptYesNo("(type 'y' <return>)"));
        $this->assertTrue(Pfw_Script_CLI::promptYesNo("(type 'yes' <return>)"));
        $this->assertTrue(Pfw_Script_CLI::promptYesNo("(type 'YES' <return>)"));
    }
    */

    /*
    function testPromptYesNoNo()
    {
        $this->assertFalse(Pfw_Script_CLI::promptYesNo("(type 'N' <return>)"));
        $this->assertFalse(Pfw_Script_CLI::promptYesNo("(type 'n' <return>)"));
    }
    */

    /*
    function testPromptYesNoDefaultYes()
    {
        $this->assertTrue(Pfw_Script_CLI::promptYesNo("(<return>)", true));
    }
    */

    /*
    function testPromptYesNoDefaultNo()
    {
        $this->assertFalse(Pfw_Script_CLI::promptYesNo("(<return>)", false));
    }
    */

    /*
    function testPromptMessage()
    {
        $this->assertEquals("hello world", Pfw_Script_CLI::promptWithMessage("(type 'hello world' <return>)"));
        $this->assertTrue("hello world" != Pfw_Script_CLI::promptWithMessage("(type 'asdf' <return>)"));
        $this->assertEquals("hello world", Pfw_Script_CLI::promptWithMessage("(<return>)", "hello world"));
    }
    */
}