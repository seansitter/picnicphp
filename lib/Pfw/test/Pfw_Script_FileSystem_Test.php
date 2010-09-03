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
Pfw_Loader::loadClass('Pfw_Script_FileSystem');

class Pfw_Script_FileSystem_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    const TEST_PROMPT = false;

    public $test_dirs = array(
        '/tmp/mypfwtestdir',
        '/tmp/mypfwtestdir2',
        '/tmp/mypfwtestdir/recursive'
    );

    function setup()
    {
        $this->_deleteTestDirs();
    }

    function tearDown()
    {
        $this->_deleteTestDirs();
        $this->callback_called = false;
    }

    public static function myCallback($file_1, $file_2)
    {
        $this->callback_called = true;
        return true;
    }

    public function _deleteTestDirs()
    {
        foreach ($this->test_dirs as $dir) {
            if (is_dir($dir)) {
                Pfw_Script_FileSystem::rmdir($dir, true);
            }
        }
    }

    public function testRmDir()
    {
        $path = '/tmp/mypfwtestdir/recursive';
        $del_path = '/tmp/mypfwtestdir';

        // test delete
        mkdir($del_path, 0755, true);
        $this->assertTrue(file_exists($del_path));
        Pfw_Script_FileSystem::rmdir($del_path, true);
        $this->assertTrue(!file_exists($del_path));

        // test recursive delete
        mkdir($path, 0755, true);
        $this->assertTrue(file_exists($path));
        Pfw_Script_FileSystem::rmdir($del_path, true);
        $this->assertTrue(!file_exists($del_path));
    }

    public function testRmDirPrompt()
    {
        $path = '/tmp/mypfwtestdir/recursive';
        $del_path = '/tmp/mypfwtestdir';

        // test recursive delete
        if (self::TEST_PROMPT) {
            mkdir($path, 0755, true);
            $this->assertTrue(file_exists($path));
            Pfw_Script_FileSystem::rmdir($del_path, true, array('prompt' => true));
            $this->assertTrue(!file_exists($del_path));
        }
    }

    function testFileExists()
    {
        $this->assertTrue(Pfw_Script_FileSystem::exists(__FILE__));
    }

    function testMkdirSucc()
    {
        $dir = '/tmp/mypfwtestdir';
        Pfw_Script_FileSystem::mkdir($dir);
        $this->assertTrue(Pfw_Script_FileSystem::exists($dir));
    }

    function testMkdirRecursive()
    {
        $dir = '/tmp/mypfwtestdir/recursive';
        Pfw_Script_FileSystem::mkdir($dir);
        $this->assertTrue(Pfw_Script_FileSystem::exists($dir));
    }

    function testMkdirExists()
    {
        $dir = '/tmp/mypfwtestdir';
        Pfw_Script_FileSystem::mkdir($dir);
        $this->assertExceptionOnFunction(
            "Pfw_Script_FileSystem::mkdir", array($dir),
            'Pfw_Exception_Script',
            Pfw_Exception_Script::E_FS_ALREADY_EXISTS
        );
    }

    function testCreateFileWithContents($prompt = null)
    {
        $file = '/tmp/mypfwtestdir/recursive/text.txt';
        $contents = "hello world";
        Pfw_Script_FileSystem::createFileWithContents(
            $file,
            $contents,
            array('prompt' => $prompt)
        );
        $this->assertTrue(file_exists($file));
        $file_contents = file_get_contents($file);
        $this->assertEquals($contents, $file_contents);
    }

    function testCreateFileWithContentsPrompt()
    {
        if (self::TEST_PROMPT) {
            $this->testCreateFileWithContents(true);
        }
    }

    function testFullCopy()
    {
        $new_file = '/tmp/mypfwtestdir2/recursive/text2.txt';
        $contents = "hello world";
        Pfw_Script_FileSystem::createFileWithContents(
            $new_file,
            $contents
        );
        Pfw_Script_FileSystem::mkdir('/tmp/mypfwtestdir2/stuff');

        $this->testFileCopy(true);

        $from_dir = '/tmp/mypfwtestdir2';
        $to_dir = '/tmp/mypfwtestdir';
        Pfw_Script_FileSystem::fullCopy($from_dir, $to_dir);

        $this->assertTrue(file_exists('/tmp/mypfwtestdir/recursive/text2.txt'));
        $this->assertTrue(file_exists('/tmp/mypfwtestdir/stuff'));
    }

    function testFileCopy($test = true)
    {
        $file = '/tmp/mypfwtestdir/recursive/text.txt';
        $new_file = '/tmp/mypfwtestdir/text.txt';
        $contents = "hello world";

        Pfw_Script_FileSystem::createFileWithContents(
            $file,
            $contents
        );

        if ($test) {
            Pfw_Script_FileSystem::fileCopy($file, $new_file);
            $this->assertTrue(file_exists($file));
            $this->assertTrue(file_exists($new_file));
            $this->assertEquals($contents, file_get_contents($new_file));
        }
    }

    function testGetFilesInDir()
    {
        $this->testFileCopy(false);
        $files = Pfw_Script_FileSystem::getFilesInDir('/tmp/mypfwtestdir/recursive');
        $this->assertEquals(array('/tmp/mypfwtestdir/recursive/text.txt'), $files);
    }

    function testGetFilesInDirRecursive()
    {
        $this->testFileCopy(false);
        $files = Pfw_Script_FileSystem::getFilesInDirRecursive('/tmp/mypfwtestdir');

        $this->assertEquals(
            array(
                '/tmp/mypfwtestdir/recursive/text.txt',
                '/tmp/mypfwtestdir/recursive'
            ),
            $files
        );
    }
}

?>