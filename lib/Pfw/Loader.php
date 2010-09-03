<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2008 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

require_once('Pfw/Exception/Loader.php');

/**
 * Wraps the php file loading functions & extends to add packaging support +
 * includes files from the correct paths for picnic
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Loader
{
    /**
     * Loads a php include containing a class from the project's lib path
     * and checks that the class was defined
     *
     * @param string $class
     */
    public static function loadClass($class, $path_prefix = null)
    {
        global $_PATHS;
        
        if (true == self::classExists($class)) {
            return true;
        }

        $file = self::getFilenameFromClassname($class);
        self::_securityCheck($file);
        
        if (!empty($path_prefix)) {
            $file = self::_normalizeConcatPath($path_prefix, $file);
        }
        self::_include($file);
        self::_assertClassExists($class);
    }


    /**
     * Loads a model from the project's model path
     *
     * @param string $model
     */
    public static function loadModel($model)
    {
        global $_PATHS;
        if (true == self::classExists($model)) {
            return true;
        }

        if (!isset($_PATHS['models'])) {
            trigger_error("The models path is not set, make sure prj_paths.php ".
                "is loaded properly", E_USER_ERROR);
        }
        $file = self::getFilenameFromClassname($model);
        self::_securityCheck($file);

        $lib_path = self::_normalizeConcatPath($_PATHS['models']);
        $file = rtrim(rtrim($lib_path, '/'), '\\')."/{$file}";
        
        self::_include($file);
        self::_assertClassExists($model);
    }


    /**
     * Loads a controller from the project's controller path
     *
     * @param string $controller
     */
    public static function loadController($controller)
    {
        global $_PATHS;
        if (true == self::classExists($controller)) {
            return true;
        }

        if (!isset($_PATHS['controllers'])) {
            trigger_error("The controllers path is not set, make sure ".
                "prj_paths.php is loaded properly", E_USER_ERROR);
        }
        
        $file = self::getFilenameFromClassname($controller);
        self::_securityCheck($file);

        $lib_path = self::_normalizeConcatPath($_PATHS['controllers']);
        $file = rtrim(rtrim($lib_path, '/'), '\\')."/{$file}";
        
        self::_include($file);
        self::_assertClassExists($controller);
    }
    
    
    /**
     * Loads a php include from the project's lib path
     *
     * @param string $include
     * @param bool $once
     */
    public static function loadInclude($include, $once = true)
    {
        global $_PATHS;
        $file = self::getFilenameFromClassname($include);
        self::_securityCheck($file);
        
        if (is_null($path_prefix)) {
            $path_prefix = "";
        }
        
        self::_include($file, true);
    }


    /**
     * Includes a file from the current include path, or from a
     * one of the specified paths
     *
     * @param string $filename
     * @param mixed $dirs
     * @param bool $once
     * @return bool
     */
    public static function loadFile($file, $dirs = null, $once = true)
    {
        self::_securityCheck($file);
        if (null !== $dirs and !empty($dirs)) {
            self::_includeByPath($file, $dirs, $once);
        } else {
            self::_include($file, $once);
        }
    }


    /**
     * Checks that a class or interface with name $class exists
     *
     * @param string $class
     * @return bool
     */
    public static function classExists($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return true;
        }
        return false;
    }


    /**
     * Verifies that a class or interface exists
     *
     * @param string $class
     */
    protected static function _assertClassExists($class)
    {
        if (!self::classExists($class)) {
            require_once('Pfw/Exception/Loader.php');
            throw new Pfw_Exception_Loader($class, Pfw_Exception_Loader::CLASS_MISSING);
        }
    }


    /**
     * Includes a file by first setting the include path, then including file
     * under that path, then resetting the include path to the previous
     *
     * @param string $file  The file to include
     * @param string $path  The absolute path to the file
     * @param bool $once
     */
    protected static function _includeByPath($file, $path, $once = true)
    {
        if (is_array($path)) {
            $path = implode(PATH_SEPARATOR, $path);
        }

        if (!empty($path)) {
            $old_path = get_include_path();
            set_include_path($path . PATH_SEPARATOR . $old_path);
        }
        self::_include($file, $once);
        if (!empty($path)) {
            set_include_path($old_path);
        }
    }

    /**
     * Do the action require
     *
     * @param $file
     * @param $once
     * @return void
     */
    protected static function _include($file, $once = true)
    {
        if ($once == true) {
            $ret = include_once($file);
        } else {
            $ret = include($file);
        }

        if ($ret === false) {
            throw new Pfw_Exception_Loader(
                $file,
                Pfw_Exception_Loader::FILE_MISSING, get_include_path()
            );
        }
        return $ret;
    }

    public static function getFilenameFromClassname($classname, $include_php_ext = true)
    {
        if (empty($classname)) {
            throw new Pfw_Exception_Loader(
                "<empty>",
                Pfw_Exception_Loader::FILENAME_EMPTY
            );
        }
        
        $file = str_replace('_', DIRECTORY_SEPARATOR, $classname);
        
        if (true == $include_php_ext) {
             $file .= '.php';
        }
        
        return $file;
    }
    
    /**
     * Ensure that filename does not contain exploits.
     * Credited to Zend Framework
     *
     * @param  string $filename
     * @return void
     * @throws Pfw_Exception_Security
     */
    protected static function _securityCheck($string)
    {
        if (preg_match('/[^a-z0-9\\/\\\\_.-]/i', $string)) {
            require_once('Pfw/Exception/Security.php');
            throw new Pfw_Exception_Security("Security check: Illegal character in filename: $string");
        }
    }
    
    public static function _normalizeConcatPath()
    {
        $path_list = func_get_args();
        if (empty($path_list)) {
            return "";
        }

        $ret = "";
        foreach ($path_list as $arg) {
            if (!empty($arg)) {
                $ret .= rtrim(rtrim($arg, '/'), '\\').DIRECTORY_SEPARATOR;
            }    
        } 
        
        return $ret;
    }
}
