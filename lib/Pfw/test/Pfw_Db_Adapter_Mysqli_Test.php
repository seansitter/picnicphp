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
Pfw_Loader::loadInclude('Pfw_Core_Base');
Pfw_Loader::loadClass('Pfw_Db_Adapter_Mysqli');

class Pfw_Db_Adapter_Mysqli_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function setup()
    {
        $this->createTables();
    }

    public function tearDown()
    {
        $this->dropTables();
    }

    public function testSprintfEsc()
    {
        $cnx = $this->getCnx();
        $user = 'sean';
        $welcome = $cnx->_sprintfEsc(array("hello %s", $user));
        $this->assertEquals("hello 'sean'", $welcome);

        $user = 'sean"';
        $welcome = $cnx->_sprintfEsc(array("hello %s", $user));
        $this->assertEquals("hello 'sean\\\"'", $welcome);
    }

    public function testInsert()
    {
        $cnx = $this->getCnx();
        $id = $cnx->insert("INSERT INTO test_table(name) VALUES('ok')");
        $this->assertGreaterThan(0, $id);
        return $id;
    }
    
    public function testInserAllIntoTable()
    {
        $cnx = $this->getCnx();
        $dataset = array(
            array('name' => 'sean'),
            array('name' => 'pete', 'count' => 15, 'notpresent' => 'yikes'),
            array('name' => 'joe', 'count' => 10)
        );
        $count = $cnx->insertAllIntoTable('test_table', $dataset);
        $this->assertEquals($count, 3);
    }

    public function testFetchOne()
    {
        $id = $this->testInsert();
        $cnx = $this->getCnx();
        $res = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '$id'");
        $this->assertEquals($res['id'], $id);
    }

    public function testFetchAll()
    {
        $id = $this->testInsert();
        $this->testInsert();
        $cnx = $this->getCnx();
        $row = $cnx->fetchAll("SELECT * FROM test_table WHERE id >= '$id'");
        $this->assertEquals($row[0]['id'], $id);
        $this->assertEquals($row[1]['id'], $id+1);
    }

    public function testUpdate()
    {
        $id = $this->testInsert();
        $cnx = $this->getCnx();
        $cnx->update("UPDATE test_table SET name = 'stuff' WHERE id = '$id'");
        $row = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '$id' LIMIT 1");
        $this->assertEquals($row['name'], 'stuff');
    }

    public function testInsertIntoTable()
    {
        $cnx = $this->getCnx();
        $id = $cnx->insertIntoTable(
            'test_table',
            array('name' => 'table_insert', 'notpresent' => 'yikes')
        );
        $this->assertGreaterThan(0, $id);
        return $id;
    }

    public function testTableUpdate()
    {
        $cnx = $this->getCnx();
        $id = $this->testInsertIntoTable();
        $cnx->updateTable('test_table', array('name' => 'table_update'), array("id = %s", $id));
        $row = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '$id'");
        $this->assertEquals('table_update', $row['name']);
    }

    public function testTextTxnVisibility()
    {
        $tx_cnx = $this->getCnxForTxn();
        $cnx = $this->getCnx();
        $tx_cnx->beginTxn();
        $id = $tx_cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $row = $tx_cnx->fetchOne("SELECT * FROM test_table ORDER BY id DESC limit 1");
        $this->assertEquals($id, $row['id']);
        $row = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$id}'");
        $this->assertTrue(empty($row));
        $tx_cnx->commitTxn();
        $row = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$id}'");
        $this->assertFalse(empty($row));
    }

    public function testTextMixedTxn()
    {
        $tx_cnx = $this->getCnxForTxn();
        $cnx = $this->getCnx();

        # begin a txn and insert a new row
        $tx_cnx->beginTxn();
        $tx_id = $tx_cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $row = $tx_cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$tx_id}'");
        $this->assertEquals($tx_id, $row['id']);

        # insert a new row without txn on different connection
        $id = $cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $row = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$id}'");
        $this->assertEquals($id, $row['id']);

        # rollback txn
        $tx_cnx->rollbackTxn();

        # verify we have non-txn row
        $row = $cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$id}'");
        $this->assertEquals($id, $row['id']);

        # verify we don't have txn rolled back row
        $row = $tx_cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$tx_id}'");
        $this->assertTrue(empty($row));
    }

    public function testMultiInsertTxnCommit()
    {
        $tx_cnx = $this->getCnxForTxn();

        # begin a txn and insert a new row
        $tx_cnx->beginTxn();
        $tx_id1 = $tx_cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $tx_id2 = $tx_cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $tx_cnx->commitTxn();

        $row = $tx_cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$tx_id1}'");
        $this->assertEquals($tx_id1, $row['id']);

        $row = $tx_cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$tx_id2}'");
        $this->assertEquals($tx_id2, $row['id']);
    }

    public function testMultiInsertTxnRollback()
    {
        $tx_cnx = $this->getCnxForTxn();

        # begin a txn and insert a new row
        $tx_cnx->beginTxn();
        $tx_id1 = $tx_cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $tx_id2 = $tx_cnx->insertIntoTable('test_table', array('name' => 'table_insert'));
        $tx_cnx->rollbackTxn();

        $row = $tx_cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$tx_id1}'");
        $this->assertTrue(empty($row));

        $row = $tx_cnx->fetchOne("SELECT * FROM test_table WHERE id = '{$tx_id2}'");
        $this->assertTrue(empty($row));
    }

    public function dropTables()
    {
        $cnx = $this->getCnx();
        $cnx->query("DROP TABLE IF EXISTS `test_table`");
    }

    public function createTables()
    {
        $cnx = $this->getCnx();
        $table_create_stmt = <<<EOS
CREATE TABLE `test_table` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `count` int(10) default NULL,
  `ts_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1
EOS;
        $cnx->query($table_create_stmt);
    }

    public function getCnx()
    {
        return Pfw_Db::factory();
    }

    public function getCnxForTxn()
    {
        return Pfw_Db::factoryForTxn();
    }
}

/*
CREATE TABLE `test_table` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `count` int(10) default NULL,
  `ts_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1
*/

?>