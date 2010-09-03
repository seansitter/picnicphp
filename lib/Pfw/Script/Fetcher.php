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

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_Fetcher
{
    public static function fetchFile($url, $dest_filename)
    {
        self::assertCurlExists();
        
        $fh = fopen($dest_filename, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        $ret = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ((false === $ret) or (200 != $status)){
            if (is_file($dest_filename)) {
                unlink($dest_filename);
            }
            if (200 !== $status) {
                throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FETCH_UNKNOWN,
                        "Got a http status {$status} when fetching: {$url}"
                );
            } else {
                throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FETCH_UNKNOWN,
                curl_error($ch)
                );
            }
        }

        if (!is_file($dest_filename)) {
            throw new Pfw_Exception_Script(
            Pfw_Exception_Script::E_FETCH_UNKNOWN,
                    "Unknown error retrieving file from: $url."
            );
        }
    }

    public static function fetchContents($url)
    {
        self::assertCurlExists();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (false === ($ret = curl_exec($ch))) {
            throw new Pfw_Exception_Script(
                Pfw_Exception_Script::E_FETCH_UNKNOWN,
                curl_error($ch)
            );
        }

        return $ret;
    }
    
    protected static function assertCurlExists()
    {
        if (!function_exists('curl_init')) {
            throw new Pfw_Exception_Script(
                Pfw_Extension_Script::E_MISSING_EXTENSION,
                "Curl is not installed. see: http://us2.php.net/manual/en/book.curl.php"
            );
        }
    }
}
