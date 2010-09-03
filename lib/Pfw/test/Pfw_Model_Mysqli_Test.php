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

/**
 * Standard request adapter provides convenience methods
 * to access to properties of the request
 *
 * @category      Framework
 * @package       Pfw
 */

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');

class Pfw_Model_Mysqli_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function setup()
    {
        $this->createTables();
    }

    public function tearDown()
    {
        $this->dropTables();
    }

    public function testTxInsert()
    {
        $tx_cnx = $this->getCnxForTxn();

        # begin a new cnx
        $tx_cnx->beginTxn();
        # create and save a foo on our new cnx
        $foo = new Foo();
        $foo->_setDb($tx_cnx);
        $foo->name = "petey";
        $foo->save();

        # should be visible from txn
        $tx_foo = new Foo($foo->id);
        $tx_foo->_setDb($tx_cnx);
        try {
            $tx_foo->retrieve();
        } catch(Exception $e) {}
        $this->assertEquals($foo->name, $tx_foo->name);

        # should not be visible from non-tx connection
        $new_foo = new Foo($foo->id);
        $ex = null;
        try {
            $new_foo->retrieve();
            print_r($new_foo);
        } catch(Exception $e) {
            $ex = $e;
        }
        $this->assertTrue(is_a($e, 'Pfw_Exception_NotFound'));

        # commit the txn
        $tx_cnx->commitTxn();

        # should now be visible outside txn
        $new_foo = new Foo($foo->id);
        try {
            $new_foo->retrieve();
        } catch(Exception $e) {}

        $this->assertEquals($new_foo->name, $foo->name);
    }

    public function testTxInsertRollback()
    {
        $tx_cnx = $this->getCnxForTxn();

        # begin a new cnx
        $tx_cnx->beginTxn();
        # create and save a foo on our new cnx
        $foo = new Foo();
        $foo->_setDb($tx_cnx);
        $foo->name = "joe";
        $foo->save();

        # should be visible from txn
        $tx_foo = new Foo($foo->id);
        $tx_foo->_setDb($tx_cnx);
        $ex = null;
        try {
            $tx_foo->retrieve();
        } catch(Exception $e) {
            $ex = $e;
        }
        $this->assertTrue(is_null($ex));
        $this->assertEquals($foo->name, $tx_foo->name);

        # rollback
        $tx_cnx->rollbackTxn();

        # should no longer be visible in txn
        $tx_foo = new Foo($foo->id);
        $tx_foo->_setDb($tx_cnx);
        $ex = null;
        try {
            $tx_foo->retrieve();
        } catch(Exception $e) {
            $ex = $e;
        }
        $this->assertTrue(is_a($ex, 'Pfw_Exception_NotFound'));
    }

    public function testTxnQo()
    {
        $tx_cnx = $this->getCnxForTxn();

        # begin a new cnx
        $tx_cnx->beginTxn();
        # create and save a foo on our new cnx
        $foo = new Foo();
        $foo->_setDb($tx_cnx);
        $foo->name = "sean";
        $foo->save();

        $tx_foo = Foo::Q($tx_cnx)->getById($foo->id)->exec();
        $this->assertEquals($foo->name, $tx_foo->name);

        $notx_foo = Foo::Q()->getById($foo->id)->exec();
        $this->assertTrue(empty($notx_foo));

        $tx_cnx->commitTxn();

        $notx_foo = Foo::Q()->getById($foo->id)->exec();
        $this->assertEquals($foo->name, $notx_foo->name);
    }

    public function createTables()
    {
        $create_foo = <<<EOT
CREATE TABLE IF NOT EXISTS `foos` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(64),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;
        $this->getCnx()->query($create_foo);
    }

    public function dropTables()
    {
        $this->getCnx()->query("DROP TABLE IF EXISTS foos");
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
