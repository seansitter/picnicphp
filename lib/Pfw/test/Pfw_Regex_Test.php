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
 * @author                Sean Sitter <sean@picnicphp.com>
 * @copyright         2008 The Picnic PHP Framework
 * @license             http://www.apache.org/licenses/LICENSE-2.0
 * @link                    http://www.picnicphp.com
 * @since                 0.10
 */

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');
Pfw_Loader::loadClass('Pfw_Controller_Router_Standard');
Pfw_Loader::loadClass('Pfw_Regex');

class Pfw_Regex_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    function testBasicPattern()
    {
        $pattern = '/a(\d+)/';
        $subject = 'a123';

        $regex = new Pfw_Regex($pattern);
        $this->assertEquals(true, $regex->exec($subject));
    }

    function testResetPatternSubject()
    {
        $pattern = '/a(\d+)/';
        $subject = 'a123';

        $regex = new Pfw_Regex($pattern);
        $this->assertEquals(true, $regex->exec($subject));

        $subject = 'b123';
        $regex->setPattern('/b123/');
        $this->assertEquals(true, $regex->exec($subject));
    }

    function testGetMatches()
    {
        $pattern = '/a(\d+)/';
        $subject = 'a123';

        $regex = new Pfw_Regex($pattern);
        $regex->exec($subject);
        $this->assertEquals(array('a123', '123'), $regex->getMatches());
    }
}

?>