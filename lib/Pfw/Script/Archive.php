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

Pfw_Loader::loadClass('Archive_Tar', 'ThirdParty');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_Archive
{
    public static function expand($path) {
        $path = realpath($path);
        $dir = dirname($path);
        #print ">> $path\n";
        $arch = new Archive_Tar($path, true);
        $arch->setErrorHandling(PEAR_ERROR_PRINT);

        if (false === $arch->extract($dir)) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_ARCHIVE_UNKNOWN
            );
        }
    }
}
