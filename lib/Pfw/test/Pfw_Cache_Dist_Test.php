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
Pfw_Loader::loadClass('Pfw_Cache_Dist');

class Pfw_Cache_Dist_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function setup()
    {
        Pfw_Config::setConfig(array(
            'dist_cache' => 'Pfw_Cache_Stub'
        ));
    }

    public function tearDown()
    {
        Pfw_Cache_Dist::_reset();
    }

    public function testSetGet()
    {
        Pfw_Cache_Dist::set('test', 'ok');
        $this->assertEquals('ok', Pfw_Cache_Dist::get('test'));
    }

    public function testAddGet()
    {
        Pfw_Cache_Dist::add('test', 'ok');
        $this->assertEquals('ok', Pfw_Cache_Dist::get('test'));
    }

    public function testSetDelete()
    {
        Pfw_Cache_Dist::set('test', 'ok');
        $this->assertEquals('ok', Pfw_Cache_Dist::get('test'));
        Pfw_Cache_Dist::delete('test');
        $this->assertEquals(null, Pfw_Cache_Dist::get('test'));
    }

    public function testOptions()
    {
        Pfw_Config::setConfig(array(
            'dist_cache' => array('class' => 'Pfw_Cache_Stub', 'stuff' => 'ok')
        ));
        $this->assertEquals(array('stuff' => 'ok'), Pfw_Cache_Dist::getInstance()->options);
    }
}

?>