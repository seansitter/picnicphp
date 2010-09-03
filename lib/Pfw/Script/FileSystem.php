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
Pfw_Loader::loadClass('Pfw_Script_Message');
Pfw_Loader::loadClass('Pfw_Script_CLI');
Pfw_Loader::loadClass('Pfw_Exception_Script');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_FileSystem
{
    public static function exists($path) {
        if (file_exists($path)) {
            return true;
        }
        return false;
    }

    public function getFilesInDir($pathname, $include_dirs = false, $include_links = true, $exclude = array('.', '..'))
    {
        if (false == ($dh = opendir($pathname))) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_UNKNOWN,
                "Failed to open directory: '$pathname'"
            );
        }

        $files = array();
        while (($entry = readdir($dh)) !== false) {
            if (!$include_links and is_link($entry)) {
                continue;
            }
            if (!$include_dirs and is_dir($entry)) {
                continue;
            }
            if (in_array($entry, $exclude)) {
                continue;
            }

            array_push($files, $pathname.DIRECTORY_SEPARATOR.$entry);
        }

        return $files;
    }

    public static function getFilesInDirRecursive($pathname, $include_links = true, $exclude = array('.', '..')) {
        if (false == ($dh = opendir($pathname))) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_UNKNOWN,
                "Failed to open directory: '$pathname'"
            );
        }

        $files = array();
        while ($entry = readdir($dh)) {
            if (in_array($entry, array('.', '..'))) {
                continue;
            }

            $entry = $pathname.DIRECTORY_SEPARATOR.$entry;
            if (is_dir($entry)) {
                self::_getFilesInDirRecursive($files, $entry, $include_links, $exclude);
                array_push($files, $entry);
                continue;
            } elseif (!$include_links and is_link($entry)) {
                continue;
            } elseif (in_array($entry, $exclude)) {
                continue;
            }
            array_push($files, $entry);
        }
        closedir($dh);

        return $files;
    }

    private static function _getFilesInDirRecursive(&$files, $pathname, $include_links = true, $exclude = array('.', '..')) {
        if (false == ($dh = opendir($pathname))) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_UNKNOWN,
                "Failed to open directory: '$pathname'"
            );
        }

        $entry = null;
        while (($entry = readdir($dh)) !== false) {
            if (in_array($entry, array('.', '..'))) {
                continue;
            }

            $entry = $pathname.DIRECTORY_SEPARATOR.$entry;
            if (is_dir($entry)) {
                self::_getFilesInDirRecursive($files, $entry, $include_links, $exclude);
                array_push($files, $entry);
                continue;
            } elseif (!$include_links and is_link($entry)) {
                continue;
            } elseif (in_array($entry, $exclude)) {
                continue;
            }
            array_push($files, $entry);
        }
        closedir($dh);
    }

    public static function mkdir($pathname, $mode = 0755, $recursive = true, $options = array())
    {
        $o = $options;
        if (file_exists($pathname)) {
            if (!empty($o['already_exists_callback'])) {
                if (false == call_user_func($o['already_exists_callback'])) {
                    throw new Pfw_Exception_Script(
                        Pfw_Exception_Script::E_CALLBACK_FAILED,
                        'aready_exists_callback'
                    );
                }
            }
            else {
                throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_ALREADY_EXISTS);
            }
        }

        if (!self::handleYesNo("Create directory '$pathname'?", $options)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
        }

        if (mkdir($pathname, $mode, $recursive)) {
            return true;
        }

        throw new Pfw_Exception_Script(
            Pfw_Exception_Script::E_FS_UNKNOWN,
            "Failed to create directory: '$pathname'."
        );
    }

    public static function rmdir($pathname, $recursive = false, $options = array())
    {
        $o = $options;
        if (!file_exists($pathname)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_NOT_FOUND);
        }
        if (is_file($pathname) or is_link($pathname)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_NOT_A_DIRECTORY);
        }

        if (true == $recursive) {
            if(is_dir($pathname)){
                if (false == ($dh = opendir($pathname))) {
                    throw new Pfw_Exception_Script(
                        Pfw_Exception_Script::E_FS_UNKNOWN,
                        "Failed to open directory: '$dir'"
                    );
                }
                while (($entry = readdir($dh)) !== false) {
                    if($entry == '.' || $entry == '..') {
                        continue;
                    }

                    $full_entry = $pathname . DIRECTORY_SEPARATOR . $entry;
                    if(is_dir($full_entry)){
                        self::rmdir($full_entry, true, $options);
                        continue;
                    }

                    // delete the file
                    if (!self::handleYesNo("Delete file '$full_entry'?", $options)) {
                        throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
                    }
                    self::unlink($full_entry, $options);
                }

                if (!self::handleYesNo("Delete directory '$pathname'?", $options)) {
                    throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
                }
                rmdir($pathname);

                closedir($dh);
            }

            return true;
        }
        else {
            if (!self::handleYesNo("Delete directory '$pathname'?", $options)) {
                throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
            }
            if (rmdir($pathname)) {
                return true;
            }
        }

        throw new Pfw_Exception_Script(
            Pfw_Exception_Script::E_FS_UNKNOW,
            "Failed to remove directory: '$pathname'."
        );
    }

    public static function unlink($filename, $options = array()) {
        $o = $options;
        if (!file_exists($filename)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_NOT_FOUND);
        }
        if (!is_file($filename) and !is_link($filename)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_NOT_A_FILE);
        }

        if (!self::handleYesNo("Delete file '$filename'?", $options)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
        }

        if (unlink($filename)) {
            return true;
        }

        throw new Pfw_Exception_Script(
            Pfw_Exception_Script::E_FS_UNKNOWN,
            "Failed to delete file '$filename'."
        );
    }

    public static function createFileWithContents($filename, $contents, $options = array())
    {
        $o = $options;
        if (file_exists($filename) and (isset($o['force']) and (true !== $o['force']))) {
            if (!empty($o['already_exists_callback'])) {
                if (false == call_user_func($o['already_exists_callback'])) {
                    throw new Pfw_Exception_Script(Pfw_Exception_Script::E_CALLBACK_FAILED);
                }
            }
            else {
                throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_ALREADY_EXISTS);
            }
        }

        $dir = dirname($filename);
        if (!is_dir($dir)) {
            $create_dir = isset($o['prompt_create_dir']) ? $o['prompt_create_dir'] : null;
            $create_msg = isset($o['prompt_create_dir_message']) ? $o['prompt_create_dir_message'] : null;
            $create_default = isset($o['prompt_create_dir_default_value']) ? $o['prompt_create_dir_message'] : null;
            self::mkdir($dir, 0755, true, array(
                'prompt' => $create_dir,
                'prompt_message' => $create_msg,
                'prompt_default_value' => $create_default
            ));
        }

        if (!self::handleYesNo("Create file '$filename'?", $options)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
        }

        if (false === ($f = fopen($filename, 'w'))) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_UNKNOWN,
                "Failed to open file '$filename' for writing."
            );
        }
        if (false === fwrite($f, $contents)) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_UNKNOWN,
                "Failed to write to file '$filename'."
            );
        }

        fclose($f);
        return true;
    }


    public static function fileCopy($source, $dest, $options = array()) {
        $o = $options;

        if ($source == $dest) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FS_SAME_FILE,
                $dest
            );
        }

        if (file_exists($dest) and (isset($o['force']) and (true != $o['force']))) {
            if (!empty($o['already_exists_callback'])) {
                if (false == call_user_func($o['already_exists_callback'], $source, $dest)) {
                    throw new Pfw_Exception_Script(Pfw_Exception_Script::E_CALLBACK_FAILED);
                }
            } else {
                throw new Pfw_Exception_Script(Pfw_Exception_Script::E_FS_ALREADY_EXISTS);
            }
        }

        if (!self::handleYesNo("Copy '$source' to '$dest'?", $options)) {
            throw new Pfw_Exception_Script(Pfw_Exception_Script::E_PROMPT_NO);
        }

        $dir = dirname($dest);
        if (!is_dir($dir)) {
            self::mkdir($dir, 0755, true);
        }

        if (true === copy($source, $dest)) {
           return true;
        }

        throw new Pfw_Exception_Script(
            Pfw_Exception_Script::E_FS_UNKNOWN,
            "Failed to copy '$source' to '$dest'."
        );
    }

    public static function fullCopy($source_dir, $dest_dir, $exclude = array('.svn'), $options = array())
    {
        $o = $options;
        if(is_dir($source_dir)){
            @mkdir($dest_dir);
            $d = dir($source_dir);

            while(false !== ($entry = $d->read())){
                if($entry == '.' || $entry == '..' || in_array($entry, $exclude)) {
                    continue;
                }

                $full_entry = $source_dir . '/' . $entry;
                if(is_dir($full_entry)){
                    self::fullCopy($full_entry, $dest_dir . '/' . $entry);
                    continue;
                }
                self::fileCopy($full_entry, $dest_dir . '/' . $entry, $o);
            }

            $d->close();
        }
        else{
            self::fileCopy($source_dir, $dest_dir, $o);
        }

        return true;
    }

    public static function isValidDirname($dirname)
    {
        return self::isValidFilename($dirname);
    }

    public static function isValidFilename($filename)
    {
        return (preg_match('/[^a-z0-9\\/\\\\_.-]/i', $filename) > 0) ? false : true;
    }

    public static function makeValidDirname($dirname)
    {
        return self::makeValidFilename($dirname);
    }

    public function makeValidFilename($filename)
    {
        return preg_replace('/[^a-z0-9\\/\\\\_.-]/i', '_', $filename);
    }

    public static function handleYesNo($message, $options)
    {
        $o = $options;
        if (!empty($o['prompt'])) {
            if (is_string($o['prompt'])) {
                $message = "{$o['prompt']}: $message";
            }
            $default = isset($o['prompt_default_value']) ? $o['prompt_default_value'] : null;
            return Pfw_Script_CLI::promptYesNo($message, $default);
        }

        return true;
    }

}
