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
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2008 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 */

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');
Pfw_Loader::loadClass('Pfw_Controller_Router_Standard');
Pfw_Loader::loadClass('Pfw_Config');

class Pfw_Config_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function setup()
    {
        global $_PATHS, $_ENVIRONMENT;
        $_PATHS['conf'] = dirname(__FILE__).DIRECTORY_SEPARATOR."conf";
        $_ENVIRONMENT = "test";

        Pfw_Config::reset();
    }

    public function testGetConfig()
    {
        global $_ENVIRONMENT;
        Pfw_Config::init();
        $conf = Pfw_Config::getConfig();
        $this->assertEquals($conf['webhost'], 'My Web Host');
        $this->assertEquals('value1', $conf['arr_outer']['arr_inner']['name1']);
        $this->assertEquals('value1', $conf['arr_outer']['arr_inner']['name1']);
    }

    public function testSetConfig()
    {
        Pfw_Config::init();
        Pfw_Config::setConfig(array('test' => 'ok'));
        $conf = Pfw_Config::getConfig();
        $this->assertEquals('ok', $conf['test']);
    }

    public function testGetConfigVar()
    {
        Pfw_Config::init();
        $webhost = Pfw_Config::get('webhost');
        $this->assertEquals('My Web Host', $webhost);
    }

    public function testSetConfigVar()
    {
        Pfw_Config::init();
        $this->assertEquals('My Web Host', Pfw_Config::get('webhost'));
        Pfw_Config::set('webhost', 'test');
        $this->assertEquals('test', Pfw_Config::get('webhost'));
    }
    
    public function testEnvOverride()
    {
    	Pfw_Config::init();
    	$expect = array(
            'arr_inner' => array(
                'name1' => 'value1',
    	        'name3' => 'value3'
            )
        );
        
    	$this->assertEquals($expect, Pfw_Config::get('arr_outer'));
    }

    public function testReset()
    {
        Pfw_Config::init();
        $conf = Pfw_Config::getConfig();
        $this->assertEquals('My Web Host', Pfw_Config::get('webhost'));
        Pfw_Config::setConfig(array('test' => 'ok'));
        $this->assertEquals('ok', Pfw_Config::get('test'));
        $this->assertEquals(null, Pfw_Config::get('webhost'));
        Pfw_Config::reset();
        Pfw_Config::init();
        $this->assertEquals(null, Pfw_Config::get('test'));
        $this->assertEquals('My Web Host', Pfw_Config::get('webhost'));
    }
}

?>