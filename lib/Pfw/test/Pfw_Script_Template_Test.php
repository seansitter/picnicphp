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
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category      Framework
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp>
 * @copyright     2008 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 */

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');
Pfw_Loader::loadClass('Pfw_Script_Template');

class Pfw_Script_Template_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    function getTplDir()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR;
    }

    function testReplace()
    {
        $tpl_file = $this->getTplDir()."script_test_template.txt";
        $ctnt = Pfw_Script_Template::createInstance($tpl_file, array('TEST_VAR' => 'FUN $STUFF'));
        $this->assertEquals('This is my new FUN $STUFF', $ctnt);
    }

    function testMissingFile()
    {
        $tpl_file = $this->getTplDir()."junk.txt";
        try {
            $ctnt = Pfw_Script_Template::createInstance($tpl_file, array('TEST_VAR' => 'FUN $STUFF'));
            $this->assertEquals(true, false, "Exception was not caught");
        } catch(Exception $e) {
            $this->assertTrue(is_a($e, 'Pfw_Exception_Script'));
            $this->assertEquals(Pfw_Exception_Script::E_FS_NOT_FOUND, $e->getCode());
        }
    }
}

?>