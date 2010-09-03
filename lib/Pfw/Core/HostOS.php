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
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Core_HostOS {
    const OS_MAC     = 1;
    const OS_WIN     = 2;
    const OS_LINUX   = 3;
    const OS_SUNOS   = 4;
    const OS_OTHER   = 5;
    
    public static function getOS()
    {
        $uname = php_uname();
        $os = strtolower($uname);
        
        if (strpos($os, 'win') === 0) {
            return self::OS_WIN;
        } elseif (strpos($os, 'darwin') === 0) {
            return self::OS_MAC;
        } elseif (strpos($os, 'linux') === 0) {
            return self::OS_LINUX;
        } elseif (strpos($os, 'sun') === 0) {
            return self::OS_SUNOS;
        }
        
        return self::OS_OTHER;
    }
    
    public static function getHostsFilePath()
    {
        if (self::OS_WIN == self::getOS()) {
            return "C:\WINDOWS\system32\drivers\etc";
        }
        return "/etc/hosts";
    }
    
    public static function getPhpMajorVersion()
    {
        $version = PHP_VERSION;
        return $version{0};
    }
    
    public static function getPhpMinorVersion()
    {
        $version = PHP_VERSION;
        return $version{2};
    }
    
    public static function getPhpReleaseVersion()
    {
        $version = PHP_VERSION;
        return $version{4};
    }
}
