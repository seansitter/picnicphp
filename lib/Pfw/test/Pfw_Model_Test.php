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
Pfw_Loader::loadClass('Pfw_Model');
Pfw_Loader::loadClass('Pfw_Db');
Pfw_Loader::loadClass('Pfw_Db_Router_Fixed');
Pfw_Loader::loadClass('Pfw_Associate_PostQuery');
Pfw_Loader::loadClass('Pfw_Db_Expr');

class Pfw_Model_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function __construct()
    {
        return parent::__construct();
    }

    public function setup()
    {
        $this->createTables();
    }

    public function tearDown()
    {
        $this->dropTables();
    }
    
    public function testInsert($test = true)
    {
        $db = Pfw_Db::factory();
        $thing = new Thing();
        $thing->name = "pete";
        $thing->d_name = "me";
        $thing->save();

        if ($test) {
            $rs = $db->fetchOne(array("SELECT * FROM things WHERE id = %s", $thing->id));
            $this->assertEquals($rs['id'], $thing->id);
            $this->assertEquals($rs['d_name'], $thing->d_name);
            $this->assertEquals($rs['name'], $thing->name);
        } else {
            return $thing;
        }
    }

    public function testRetrieve()
    {
        $thing = $this->testInsert(false);

        $t2 = new Thing($thing->id);
        $t2->retrieve();
        // need to fix object equality
#        $this->assertEquals((array)$thing, (array)$t2);
    }

    public function testMissingRetrieve()
    {
        $thing = new Thing(12213432423);
        try {
            $thing->retrieve();
            $this->assertEquals(true, false, "We should never get here, missed exception.");
        } catch(Exception $e) {
            $this->assertTrue(is_a($e, 'Pfw_Exception_NotFound'));
        }
    }

    public function testDelete()
    {
        $thing = new Thing();
        $thing->name = 'sean';
        $thing->save();
        $id = $thing->getId();

        $thing->delete();
        $this->assertEquals(null, $thing->getId());
        $t = new Thing($id);
        $exc = null;
        try {
            $t->retrieve();
        } catch(Exception $e) {
            $exc = $e;
            $this->assertTrue(is_a($e, 'Pfw_Exception_NotFound'));
        }
        $this->assertNotEquals(null, $exc);
    }

    public function testSave()
    {
        $thing = $this->testInsert(false);

        $thing->name = "joe test";
        $thing->save();

        $t1 = new Thing($thing->id);
        $t1->retrieve();

        $this->assertEquals("joe test", $t1->name);
    }

    public function testAssociate($test = true)
    {
        $thing = $this->testInsert(false);

        $bar = new Bar();
        $bar->bar_name = "sammy";
        $thing->associateWith($bar);

        if ($test) {
            $this->assertEquals($bar->thing_id, $thing->id);
            $b = new Bar($bar->id);
            $b->retrieve();
            $this->assertEquals($b->thing_id, $thing->id);
        } else {
            return $thing;
        }
    }

    public function testRetrieveWith()
    {
        $thing = $this->testAssociate(false);

        $t1 = new Thing($thing->id);
        $t1->retrieve(array('with' => 'bar'));
        $this->assertTrue(is_a($t1->bar, 'Bar'));
    }
    
    public function testRetrieveWithMany()
    {
        $thing = new Thing();
        $thing->name = 'mything';
        $thing->save();
        
        $foo = new Foo();
        $foo->name = 'sean';
        $foo->save();
        $thing->associateWith($foo);
        
        $foo = new Foo();
        $foo->name = 'sammy';
        $foo->save();
        $thing->associateWith($foo);
        
        $thing = new Thing($thing->id);
        $thing->retrieve(array('with' => 'foos'));
        $this->assertTrue(is_array($thing->foos));
        $this->assertEquals('sean', $thing->foos[0]->name);
        $this->assertEquals('sammy', $thing->foos[1]->name);
    }
    
    public function testRetrieveWithManyImplicit()
    {
        $thing = new Thing();
        $thing->name = 'mything';
        $thing->save();
        
        $foo = new Foo();
        $foo->name = 'sean';
        $foo->save();
        $thing->associateWith($foo);
        
        $foo = new Foo();
        $foo->name = 'sammy';
        $foo->save();
        $thing->associateWith($foo);
        
        $thing = new Thing($thing->id);
        $thing->retrieve();
        
        $foos = $thing->foos;
        $this->assertTrue(is_array($foos));
        $this->assertEquals('sean', $foos[0]->name);
        $this->assertEquals('sammy', $foos[1]->name);
    }

    public function testAssociateBelongsTo()
    {
        $thing = $this->testInsert(false);

        $bar = new Bar();
        $bar->bar_name = "sammy";
        $bar->associateWith($thing);
        $bar->save();

        $t = new Thing($thing->id);
        $t->retrieve(array('with' => 'bar'));
        $this->assertEquals($bar->bar_name, $t->bar->bar_name);
        $this->assertEquals($bar->thing_id, $t->bar->thing_id);
    }

    public function testQ()
    {
        $thing = new Thing();
        $thing->name = "kate";
        $thing->save();

        $thing = new Thing();
        $thing->name = "marcus";
        $thing->save();

        $things = Thing::Q()->exec();

        $this->assertEquals('kate', $things[0]->name);
        $this->assertEquals('marcus', $things[1]->name);

        $this->assertEquals(2, count($things));
        foreach ($things as $thing) {
            $this->assertTrue(is_a($thing, 'Thing'));
        }
    }

    public function testQToString() {
        $thing = new Thing();
        $thing->name = "kate";
        $thing->save();

        $foo = new Foo();
        $foo->name = "steve";
        $thing->associateWith($foo);

        $sql = Thing::Q()->with('foos')->exec(null, true);
        $this->assertNotNull($sql);
    }

    public function testQWithMany()
    {
        $thing = new Thing();
        $thing->name = "kate";
        $thing->save();

        $thing = new Thing();
        $thing->name = "marcus";
        $thing->save();

        $foo = new Foo();
        $foo->name = "steve";
        $thing->associateWith($foo);

        $things = Thing::Q()->with('foos')->exec();

        $this->assertEquals(2, count($things));
        $this->assertEquals(array(), $things[0]->foos);
        $this->assertEquals(1, count($things[1]->foos));
        $this->assertTrue(is_a($things[1]->foos[0], 'Foo'));
    }

    public function testQWithOneAndMany()
    {
        $thing = new Thing();
        $thing->name = "kate";
        $thing->save();

        $bar = new Bar();
        $bar->bar_name = "sammy";
        $thing->associateWith($bar);

        $thing = new Thing();
        $thing->name = "marcus";
        $thing->save();

        $bar = new Bar();
        $bar->bar_name = "Al";
        $thing->associateWith($bar);

        $foo = new Foo();
        $foo->name = "steve";
        $thing->associateWith($foo);

        $things = Thing::Q()->with('foos')->with('bar')->exec();
        $this->assertEquals(2, count($things));
        $this->assertEquals(array(), $things[0]->foos);
        $this->assertTrue(is_a($things[0]->bar, 'Bar'));
        $this->assertEquals(1, count($things[1]->foos));
        $this->assertTrue(is_a($things[1]->foos[0], 'Foo'));
    }

    public function testImplicitRetrieveWith()
    {
        $thing = $this->testInsert(false);
        $foo = new Foo();
        $foo->name = 'shino';
        $thing->associateWith($foo);

        $foo = new Foo();
        $foo->name = 'ann';
        $thing->associateWith($foo);

        $thing = new Thing($thing->id);
        $thing->retrieve();
        $this->assertEquals(2, count($thing->foos));
    }

    public function testQWhereWith(){
        $thing = new Thing();
        $thing->name = 'pete';
        $thing->save();

        $thing = new Thing();
        $thing->name = 'kristin';
        $thing->save();

        $foo = new Foo();
        $foo->name = 'dave';
        $thing->associateWith($foo);

        $foo = new Foo();
        $foo->name = 'sammy';
        $thing->associateWith($foo);

        $things = Thing::Q()->where(array("this.name = %s", 'kristin'))->with('foos')->exec();

        $this->assertEquals(1, count($things));
        $this->assertEquals(2, count($things[0]->foos));
        $this->assertTrue(is_a($things[0]->foos[0], 'Foo'));
    }

    public function testBelongsTo(){
        $t = new Thing();
        $t->name = "sean";
        $t->save();

        $f = new Foo();
        $f->name = "kate";
        $t->associateWith($f);

        $t1 = new Thing($t->id);
        $t1->retrieve();

        $f1 = new Foo($f->id);
        $f1->retrieve(array('with' => 'thing'));

        $this->assertTrue($t1->equals($f1->thing));
    }

    public function testBelongsToWithPostQuery(){
        $t = new Thing();
        $t->name = "sean";
        $t->save();

        $f = new Foo();
        $f->name = "kate";
        $t->associateWith($f);

        $t1 = new Thing($t->id);
        $t1->retrieve();

        $f1 = new Foo($f->id);
        $f1->retrieve(
            array('with' =>
                array('thing' => array('join_strategy' => 'Pfw_Associate_PostQuery'))
        ));

        $this->assertTrue($t1->equals($f1->thing));
    }

    public function testBelongsToWithImmediate(){
        $t = new Thing();
        $t->name = "sean";
        $t->save();

        $f = new Foo();
        $f->name = "kate";
        $t->associateWith($f);

        $t1 = new Thing($t->id);
        $t1->retrieve();

        $f1 = new Foo($f->id);
        $f1->retrieve(array('with' => array('thing' => array('join_strategy' => 'Immediate'))));

        $this->assertTrue($t1->equals($f1->thing));
    }

    public function testQWithManyThru()
    {
        $user = new User();
        $user->first_name = 'sean';
        $user->save();

        $group = new Group();
        $group->name = "Sean's Group";
        $group->save();

        $user->associateWith($group);

        $users = User::Q()->with('groups')->exec();
        $this->assertEquals("Sean's Group", $users[0]->groups[0]->name);
        $this->assertEquals($users[0]->id, $users[0]->groups[0]->member->user_id);
        $this->assertEquals('sean', $users[0]->first_name);
        $this->assertTrue(is_a($users[0]->groups[0]->member, 'Pfw_Model_Thru'));

        $groups = Group::Q()->with('user')->exec();
        $this->assertEquals("Sean's Group", $groups[0]->name);
        $this->assertEquals("sean", $groups[0]->user->first_name);
        $this->assertEquals($user->id, $groups[0]->user->member->user_id);
    }

    public function testQWithManyThruReverse()
    {
        $user = new User();
        $user->first_name = 'sean';
        $user->save();

        $group = new Group();
        $group->name = "Sean's Group";
        $group->save();

        $group->associateWith($user);

        $users = User::Q()->with('groups')->exec();
        $this->assertEquals("Sean's Group", $users[0]->groups[0]->name);
        $this->assertEquals($users[0]->id, $users[0]->groups[0]->member->user_id);
        $this->assertEquals('sean', $users[0]->first_name);
        $this->assertTrue(is_a($users[0]->groups[0]->member, 'Pfw_Model_Thru'));

        $groups = Group::Q()->with('user')->exec();
        $this->assertEquals("Sean's Group", $groups[0]->name);
        $this->assertEquals("sean", $groups[0]->user->first_name);
        $this->assertEquals($user->id, $groups[0]->user->member->user_id);
    }

    public function testQWithManyThruPostQuery()
    {
        $user = new User();
        $user->resetAssociationDescription('groups');
        $user->hasMany('groups',
            array('thru' => 'member', 'default_strategy' => 'Pfw_Associate_PostQuery')
        );

        $user->first_name = 'sean';
        $user->save();

        $group = new Group();
        $group->name = "Sean's Group";
        $group->save();

        $user->associateWith($group);

        $users = User::Q()->with('groups')->exec();
        $this->assertEquals("Sean's Group", $users[0]->groups[0]->name);
        $this->assertEquals($users[0]->id, $users[0]->groups[0]->member->user_id);
        $this->assertEquals('sean', $users[0]->first_name);
        $this->assertTrue(is_a($users[0]->groups[0]->member, 'Pfw_Model_Thru'));

        $u = new User($user->id);
        $u->retrieve();
        $groups = $u->groups;

        $this->assertEquals("Sean's Group", $u->groups[0]->name);
        $this->assertEquals($users[0]->id, $u->groups[0]->member->user_id);
    }

    public function testDeleteThru()
    {
        $user = new User();
        $user->first_name = 'sean';
        $user->save();

        $group = new Group();
        $group->name = "Sean's Group";
        $group->save();

        $this->assertTrue(empty($u->groups));
        $user->associateWith($group);
        $u = new User($user->id);
        $groups = $u->groups;
        $this->assertTrue(!empty($groups));

        $u->groups[0]->delete();

        $u2 = new User($user->id);
        $groups = $u2->groups;
        $this->assertTrue(empty($u2->groups));
    }

    public function testQWithJoinConditions()
    {
        $thing = new Thing();
        $thing->name = 'pete';
        $thing->save();

        $foo = new Foo();
        $foo->name = 'sammy';
        $foo->is_deleted = 0;
        $thing->associateWith($foo);

        $thing = new Thing();
        $thing->name = 'kristin';
        $thing->save();

        $f1 = new Foo();
        $f1->name = 'dave';
        $f1->is_deleted = 1;
        $thing->associateWith($f1);

        $f2 = new Foo();
        $f2->name = 'kate';
        $f2->is_deleted = 1;
        $thing->associateWith($f2);

        $things = Thing::Q()->with('deleted_foos')->exec();

        foreach ($things as $thing) {
            $this->assertTrue(is_a($thing, 'Thing'));
        }
        $this->assertTrue(is_array($things[0]->deleted_foos));
        $this->assertTrue(empty($things[0]->deleted_foos));

        $this->assertEquals(2, count($things[1]->deleted_foos));
        $this->assertEquals($f1->id, $things[1]->deleted_foos[0]->id);
        $this->assertEquals($f2->id, $things[1]->deleted_foos[1]->id);
    }

    public function testQWithJoinConditionsPQ() {
        $thing = new Thing();

        $thing->hasMany('deleted_foos', array(
            'default_strategy' => 'Pfw_Associate_PostQuery',
            'table' => 'foos',
            'class' => 'Foo',
            'conditions' => 'deleted_foos.is_deleted = 1'
        ));

        $thing->name = 'pete';
        $thing->save();

        $foo = new Foo();
        $foo->name = 'sammy';
        $foo->is_deleted = 0;
        $thing->associateWith($foo);

        $thing = new Thing();
        $thing->name = 'kristin';
        $thing->save();

        $f1 = new Foo();
        $f1->name = 'dave';
        $f1->is_deleted = 1;
        $thing->associateWith($f1);

        $f2 = new Foo();
        $f2->name = 'kate';
        $f2->is_deleted = 1;
        $thing->associateWith($f2);

        $things = Thing::Q()->with('deleted_foos')->exec();

        foreach ($things as $thing) {
            $this->assertTrue(is_a($thing, 'Thing'));
        }
        $this->assertTrue(is_array($things[0]->deleted_foos));
        $this->assertTrue(empty($things[0]->deleted_foos));

        $this->assertEquals(2, count($things[1]->deleted_foos));
        $this->assertEquals($f1->id, $things[1]->deleted_foos[0]->id);
        $this->assertEquals($f2->id, $things[1]->deleted_foos[1]->id);
    }

    public function testGetById()
    {
        $thing = new Thing();
        $thing->name = "sean";
        $thing->save();

        $qo = Thing::Q()->getById($thing->id);
        $t = $qo->exec();
        $this->assertTrue(is_a($t, 'Thing'));
        $this->assertEquals($thing->id, $t->id);
    }

    public function testMissingGetById()
    {
        $t = Thing::Q()->getById("12213213023aa")->exec();
        $this->assertEquals(null, $t);
    }
    
    public function testAddFetchField()
    {
        $u = new User();
        $u->username = "sean";
        $u->save();

        $u1 = new User($u->getId());
        $u1->retrieve();
        
        $qo = User::Q()->getById($u->getId());
        $u2 = $qo->exec();
        //print $qo->_getSelect()->__toString();
        $g = new Group();
        $g->name = "my group";
        $g->save();
        $u2->associateWith($g);
        $u2->save();
        
        $u2 = new User($u2->getId());
        $u2->retrieve(array('with' => 'groups'));
        
        $this->assertEquals('hello sean', $u1->hello);
        $this->assertEquals('group name: my group', $u2->groups[0]->test_field);
    }
    
    public function setLimitedInsert()
    {
    	$u = new User();
    	$u->first_name = "sammy";
    	$u->last_name = "davis";
    	$u->save(array('fields' => 'first_name'));
    	
    	$u = new User($u->id);
    	$u->retrieve();
    	
    	$this->assertEmpty($u->last_name);
    }
    
    public function testLimitedUpdate()
    {
    	$u = new User();
        $u->first_name = "sammy";
        $u->last_name = "davis";
        $u->save();
        
        $u->first_name = "jeremiah";
        $u->last_name = "smith";
        $u->save(array('fields' => array('first_name')));
        
        $u = new User($u->id);
        $u->retrieve();
        
        $this->assertEquals('jeremiah', $u->first_name);
        $this->assertEquals('davis', $u->last_name);
    }
    
    public function testSpecificSaveFilter()
    {
    	$u = new User();
    	$u->first_name = "sammy";
    	$u->save(array('filter_method' => 'testSaveFilter'));
    	
    	$u = new User($u->id);
    	$u->retrieve();
    	$e = "filtered: sammy";
    	
        $this->assertEquals($e, $u->first_name);
    }
    
    public function testSpecificValidateFilter()
    {
        $u = new User();
        $u->email = "sammy";
        $u->save(array('validate_method' => 'emailValidate'));
        
        $this->assertTrue($u->hasErrors());
    }
    
    public function testUpdateAll()
    {
        $u = new User();
        $u->first_name = "sean";
        $u->last_name = "smith";
        $u->save();

        $u = new User();
        $u->first_name = "sean";
        $u->last_name = "jones";
        $u->save();
        
        $u = new User();
        $u->first_name = "jim";
        $u->last_name = "jones";
        $u->save();
        
        $updated_count = User::Q()->updateAll(array('first_name' => "pete"), array('first_name = %s', 'sean'));
        $users = User::Q()->exec();
        $this->assertEquals(2, $updated_count);
        $this->assertEquals(3, count($users));
        $this->assertEquals($users[0]->first_name, 'pete');
        $this->assertEquals($users[1]->first_name, 'pete');
        $this->assertEquals($users[2]->first_name, 'jim');
    }
    
    public function testDeleteAll()
    {
        $u = new User();
        $u->first_name = "sean";
        $u->last_name = "smith";
        $u->save();

        $u = new User();
        $u->first_name = "sean";
        $u->last_name = "jones";
        $u->save();
        
        $u = new User();
        $u->first_name = "jim";
        $u->last_name = "jones";
        $u->save();
        
        $updated_count = User::Q()->deleteAll(array('first_name = %s', 'sean'));
        $users = User::Q()->exec();
        $this->assertEquals(2, $updated_count);
        $this->assertEquals(1, count($users));
        $this->assertEquals($users[0]->first_name, 'jim');
    }

    public function createTables(){
        $create_thing = <<<EOT
CREATE TABLE IF NOT EXISTS `things` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  `d_name` varchar(64) default "jimbo",
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
        Pfw_Db::factory()->query($create_thing);

        $create_foo = <<<EOT
CREATE TABLE IF NOT EXISTS `foos` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `thing_id` bigint(20) unsigned NOT NULL,
  `name` varchar(64),
  `is_deleted` tinyint(1) unsigned default 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
        Pfw_Db::factory()->query($create_foo);

        $create_foo = <<<EOT
CREATE TABLE IF NOT EXISTS `membership` (
  `thing_id` bigint(20) unsigned NOT NULL,
  `foo_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
        Pfw_Db::factory()->query($create_foo);

        $create_bar = <<<EOT
CREATE TABLE IF NOT EXISTS `bars` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `thing_id` bigint(20) unsigned NOT NULL,
  `bar_name` varchar(64),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
        Pfw_Db::factory()->query($create_bar);

        $create_users = <<<EOS
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(128) default NULL,
  `first_name` varchar(128) default NULL,
  `last_name` varchar(128) default NULL,
  `email` varchar(128) default NULL,
  `password` varchar(64) default NULL,
  `ts_created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;
        Pfw_Db::factory()->query($create_users);

        $create_members = <<<EOS
CREATE TABLE IF NOT EXISTS `members` (
    `user_id` int(10) unsigned NOT NULL,
    `group_id` int(10) unsigned NOT NULL,
    `since` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;
        Pfw_Db::factory()->query($create_members);

        $create_groups = <<<EOS
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1
EOS;
        Pfw_Db::factory()->query($create_groups);

    }

    public function dropTables(){
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS things");
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS foos");
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS bars");
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS users");
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS members");
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS membership");
        Pfw_Db::factory()->query("DROP TABLE IF EXISTS groups");
    }
}


/**
 * Thing test model
 */
class Thing extends Pfw_Model {
    protected static $_query_object;

    public function setupAssociations()
    {
        /*
        $this->hasMany('foos', array(
            'default_strategy' => 'Pfw_Associate_PostQuery'
        ));
        $this->hasOne('bar', array(
            'default_strategy' => 'Immediate'
        ));
        */

        $this->hasMany('foos', array(
            'default_strategy' => 'Immediate',
        ));
        $this->hasMany('deleted_foos', array(
            'default_strategy' => 'Immediate',
            'table' => 'foos',
            'class' => 'Foo',
            'conditions' => 'deleted_foos.is_deleted = 1'
        ));
        $this->hasOne('bar');
    }

    public static function Q($db = null)
    {
        $qo = __CLASS__."_QueryObject";
        return new $qo(__CLASS__, $db);
    }
}

class Thing_QueryObject extends Pfw_Model_QueryObject { }


/**
 * Foo test model
 */
class Foo extends Pfw_Model {
    protected static $_query_object;

    public function setupAssociations()
    {
        $this->belongsTo('thing', array(
            'default_strategy' => 'Pfw_Associate_PostQuery'
        ));
    }

    public static function Q($db = null)
    {
        $qo = __CLASS__."_QueryObject";
        return new $qo(__CLASS__, $db);
    }

}

class Foo_QueryObject extends Pfw_Model_QueryObject { }


/**
 * Bar test model
 */
class Bar extends Pfw_Model {
    protected static $_query_object;

    public function setupAssociations()
    {
        $this->belongsTo('thing', array(
            'default_strategy' => 'Pfw_Associate_PostQuery'
        ));
    }

    public static function Q($db = null)
    {
        $qo = __CLASS__."_QueryObject";
        return new $qo(__CLASS__, $db);
    }

}

class Bar_QueryObject extends Pfw_Model_QueryObject { }


/**
 * User test model
 */
class User extends Pfw_Model {
    protected static $_query_object;
    
    public function setup()
    {
        $this->setFetchFields(
            array(
                'hello' => 
                new Pfw_Db_Expr("CONCAT('hello', ' ', {*username*})")
            )
        );
    }
    
    public function testSaveFilter(&$data, $save_type)
    {
    	$data['first_name'] = "filtered: {$data['first_name']}";
    }
    
    public function emailValidate($save_method)
    {
    	Pfw_Loader::loadClass('Pfw_Validate');
    	$v = new Pfw_Validate($this);
    	$v->email('email');
    	return $v->success();
    }

    public function setupAssociations()
    {
        $this->hasMany('groups', array(
            'thru' => 'member',
            'default_strategy' => 'Immediate'
        ));
    }

    public static function Q($db = null)
    {
        $qo = __CLASS__."_QueryObject";
        return new $qo(__CLASS__, $db);
    }

}

class User_QueryObject extends Pfw_Model_QueryObject {}


/**
 * Group test model
 */
class Group extends Pfw_Model {
    protected static $_query_object;
    
    public function setup()
    {
        $this->setFetchFields(
            array(
                'test_field' => 
                new Pfw_Db_Expr("CONCAT('group name:', ' ', {*name*})")
            )
        );
    }

    public function setupAssociations()
    {
        $this->hasOne('user', array(
            'thru' => 'member',
            'default_strategy' => 'Immediate'
        ));
    }

    public static function Q($db = null)
    {
        $qo = __CLASS__."_QueryObject";
        return new $qo(__CLASS__, $db);
    }

}

class Group_QueryObject extends Pfw_Model_QueryObject {}


?>