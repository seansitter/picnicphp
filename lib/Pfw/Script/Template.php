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

Pfw_Loader::loadClass('Pfw_Script_FileSystem');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_Template
{
    public function createInstance($template, $replacements) {
        if (!is_file($template) and !is_link($template)) {
            Pfw_Loader::loadClass('Pfw_Exception_Script');
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_NOT_FOUND,
                $template
            );
        }

        if(false === ($ctnt = file_get_contents($template))) {
            Pfw_Loader::loadClass('Pfw_Exception_Script');
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_UNKNOWN,
                "Failed to get the contents of file '$template'."
            );
        }

        foreach ($replacements as $name => $value) {
            $ctnt = preg_replace("/\\$\{$name\}/", $value, $ctnt);
        }
        return $ctnt;
    }

    public function saveInstanceToFile($template, $to_filename, $replacements = array(), $delete_template = false) {
        $content = self::createInstance($template, $replacements);
        Pfw_Script_FileSystem::createFileWithContents($to_filename, $content);

        if ($delete_template) {
            Pfw_Script_FileSystem::unlink($template);
        }
    }
}
