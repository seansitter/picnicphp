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
Pfw_Loader::loadClass('Pfw_Db');
Pfw_Loader::loadClass('Pfw_Db_Adapter_Mysqli');

class Pfw_Db_Select_Mysqli_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function setup()
    {
        $this->createTables();
    }

    public function tearDown()
    {
        $this->dropTables();
    }

    public function testFrom()
    {
        $uids = $this->insertUsers(5);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('*'));
        $rows = $sel->exec();
        $this->assertEquals(5, count($rows));
        // sample one record
        $t_rows = array(
            'id' => '1',
            'username' => 'testuser_1',
            'first_name' => 'first_name_1',
            'last_name' => 'last_name_1',
            'password' => 'password_1'
        );
        unset($rows[0]['ts_created']);
        $this->assertEquals($t_rows, $rows[0]);
    }

    public function testFromFields()
    {
        $uids = $this->insertUsers(5);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('id', 'fn' => 'first_name'));
        $rows = $sel->exec();
        $this->assertEquals(5, count($rows));
        $t_rows = array(
            'id' => '1',
            'fn' => 'first_name_1',
        );
        $this->assertEquals($t_rows, $rows[0]);
    }

    public function testWhere()
    {
        $uids = $this->insertUsers(5);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('id', 'fn' => 'first_name'))
            ->where('u.id = 5');
        $rows = $sel->exec();
        $this->assertEquals(1, count($rows));
        $t_rows = array(
            'id' => '5',
            'fn' => 'first_name_5',
        );
        $this->assertEquals($t_rows, $rows[0]);
    }

    public function testWhereGt()
    {
        $uids = $this->insertUsers(5);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('id', 'first_name'))
            ->where('u.id > 2');
        $rows = $sel->exec();
        $this->assertEquals(3, count($rows));
        $t_rows = array(
            'id' => '3',
            'first_name' => 'first_name_3',
        );
        $this->assertEquals($t_rows, $rows[0]);
    }

    public function testLimit()
    {
        $uids = $this->insertUsers(5);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('id', 'first_name'))
            ->where('u.id > 2')
            ->limit(2);
        $rows = $sel->exec();
        $this->assertEquals(2, count($rows));
        $t_rows = array(
            'id' => '3',
            'first_name' => 'first_name_3',
        );
        $this->assertEquals($t_rows, $rows[0]);
    }

    public function testLimitOffset()
    {
        $uids = $this->insertUsers(5);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('id', 'first_name'))
            ->where('u.id > 2')
            ->limit(2)
            ->offset(1);
        $rows = $sel->exec();
        $this->assertEquals(2, count($rows));
        $t_rows = array(
            'id' => '4',
            'first_name' => 'first_name_4',
        );
        $this->assertEquals($t_rows, $rows[0]);
    }

    public function testJoinInner()
    {
        $uids = $this->insertUsers(5);
        $order_ids = $this->insertOrders(3);
        $this->assocUidWithOrders($uids[0], $order_ids);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users', '*'))
            ->joinInner(
                array('o' => 'orders'),
                array('u.id = o.user_id'),
                array('*')
              );
        $rows = $sel->exec();
        $this->assertEquals(3, count($rows));
    }

    public function testJoinLeft()
    {
        $uids = $this->insertUsers(5);
        $order_ids = $this->insertOrders(3);
        $this->assocUidWithOrders($uids[0], $order_ids);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('*'))
            ->joinLeft(
                array('o' => 'orders'),
                array('u.id = o.user_id'),
                array('item_name')
              );
        $rows = $sel->exec();
        $this->assertEquals(7, count($rows));
        for ($i = 3; $i <= 6; $i++) {
            $this->assertEquals(null, $rows[$i]['item_name']);
        }
    }

    public function testDoubleJoinLeft()
    {
        $group_ids = $this->insertGroups(1);
        $user_ids = $this->insertUsers(1);
        $user_id = $user_ids[0];
        $group_id = $group_ids[0];
        $this->addUserToGroup($user_id, $group_id);
        $sel = $this->getSelectBuilder();

        $sel->from(array('u' => 'users'), array('*'))
            ->joinLeft(
                array('m' => 'members'),
                array('u.id = m.user_id'),
                array('since', 'user_id', 'group_id')
              )
            ->joinLeft(
                array('g' => 'groups'),
                array('m.group_id = g.id'),
                array('name')
              );
        $rs = $sel->exec();

        $d = $rs[0];
        $this->assertEquals($user_id, $d['user_id']);
        $this->assertEquals($group_id, $d['group_id']);
    }

    public function testJoinCross()
    {
        $uids = $this->insertUsers(5);
        $order_ids = $this->insertOrders(3);
        $this->assocUidWithOrders($uids[0], $order_ids);
        $sel = $this->getSelectBuilder();
        $sel->from(array('u' => 'users'), array('*'))
            ->joinCross(
                array('o' => 'orders'),
                array('item_name')
              );
        $rows = $sel->exec();
        $this->assertEquals(15, count($rows));
    }

    public function insertUsers($count)
    {
        $cnx = $this->getCnx();
        $ids = array();
        for ($i = 1; $i <= $count; $i++) {
            $id = $cnx->insert(
              "INSERT INTO users(username, first_name, last_name, password) ".
              "VALUES('testuser_{$i}', 'first_name_$i', 'last_name_$i', 'password_$i')"
            );
            array_push($ids, $id);
        }
        return $ids;
    }

    public function insertOrders($count)
    {
        $cnx = $this->getCnx();
        $ids = array();
        for ($i = 1; $i <= $count; $i++) {
            $id = $cnx->insert(
              "INSERT INTO orders(item_id, item_name, item_price) ".
              "VALUES('{$i}', 'item_name_$i', '$i')"
            );
            array_push($ids, $id);
        }
        return $ids;
    }

    public function insertGroups($count)
    {
        $cnx = $this->getCnx();
        $ids = array();
        for ($i = 1; $i <= $count; $i++) {
            $id = $cnx->insert(
              "INSERT INTO groups(name) ".
              "VALUES('name_{$i}')"
            );
            array_push($ids, $id);
        }
        return $ids;
    }

    public function addUserToGroup($user_id, $group_id)
    {
        $cnx = $this->getCnx();
        $cnx->insert(
            "INSERT INTO members(user_id, group_id) VALUES({$user_id}, {$group_id})"
        );
    }

    public function assocUidWithOrders($uid, $order_ids)
    {
        $cnx = $this->getCnx();
        $order_ids = implode($order_ids, ', ');
        $cnx->update("UPDATE orders SET user_id = '$uid' WHERE orders.id IN($order_ids)");
    }

    public function getSelectBuilder()
    {
        return $this->getCnx()->getSelectBuilder();
    }

    public function dropTables()
    {
        $cnx = $this->getCnx();
        $cnx->query("DROP TABLE IF EXISTS `users`");
        $cnx->query("DROP TABLE IF EXISTS `orders`");
        $cnx->query("DROP TABLE IF EXISTS `members`");
        $cnx->query("DROP TABLE IF EXISTS `groups`");
    }

    public function getCnx()
    {
        return Pfw_Db::factory();
    }

    public function createTables()
    {
        $cnx = $this->getCnx();
        $user_create_stmt = <<<EOS
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(128) default NULL,
  `first_name` varchar(128) default NULL,
  `last_name` varchar(128) default NULL,
  `password` varchar(64) default NULL,
  `ts_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;

        $members_create_stmt = <<<EOS
CREATE TABLE IF NOT EXISTS `members` (
    `user_id` int(10) unsigned NOT NULL,
    `group_id` int(10) unsigned NOT NULL,
    `since` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;

        $groups_create_stmt = <<<EOS
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;

        $orders_create_stmt = <<<EOS
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned default NULL,
  `item_id` int(10) unsigned default NULL,
  `item_name` varchar(128) default NULL,
  `item_price` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;
        $cnx->query($user_create_stmt);
        $cnx->query($members_create_stmt);
        $cnx->query($groups_create_stmt);
        $cnx->query($orders_create_stmt);
    }
}

?>