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

Pfw_Loader::loadClass('Pfw_Exception_Script');
Pfw_Loader::loadClass('Pfw_Script_FileSystem');
Pfw_Loader::loadClass('Pfw_Db_Migrate');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_Migration
{
    protected $migration_file;

    public function __construct($migration_file, $dry_run = false)
    {
        if (true == $dry_run) {
            Pfw_Db_Migrate::$_dry_run = true;
        }

        $this->migration_file = $migration_file;
        include $this->migration_file;
        $this->_migrate = $migrate;
        $this->_undo = $undo;
    }

    public function migrate() {
        $_m = $this->_migrate;
        if (empty($_m)) {
            return false;
        }
        
        $ret = $_m();
        unset($_m);

        if (false === $ret) {
            return false;
        }
        return true;
    }

    public function undo() {
        $_u = $this->_undo;
        if (empty($_u)) {
            return false;
        }
        
        $ret = $_u();
        unset($_u);

        if (false === $ret) {
            return false;
        }
        return true;
    }

    public static function getNextFilename($table, $migration_type, $adapter = 'mysql')
    {
        global $_PATHS;
        $files = Pfw_Script_FileSystem::getFilesInDir($_PATHS['deltas']);

        $next_int = time();
        $deltas_path = rtrim(rtrim($_PATHS['deltas'], '/'), "\\\\");
        $ds = DIRECTORY_SEPARATOR;

        return "{$deltas_path}{$ds}{$next_int}-{$adapter}-{$table}_{$migration_type}.sql";
    }

}
