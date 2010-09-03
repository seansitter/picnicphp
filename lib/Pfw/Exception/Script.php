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

Pfw_Loader::loadClass('Pfw_Exception');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Exception_Script extends Pfw_Exception {
    const E_UNKNOWN                 = 1;
    const E_MISSING_EXTENSION       = 2;
    const E_CALLBACK_FAILED         = 3;
    const E_PROMPT_NO               = 4;
    const E_PROMPT_YES              = 5;
    const E_FS_UNKNOWN              = 10;
    const E_FS_NOT_FOUND            = 11;
    const E_FS_NOT_A_FILE           = 12;
    const E_FS_NOT_A_DIRECTORY      = 13;
    const E_FS_IS_A_FILE            = 13;
    const E_FS_IS_A_DIRECTORY       = 14;
    const E_FS_ALREADY_EXISTS       = 15;
    const E_FS_SAME_FILE            = 16;
    const E_FS_PERMISSION_DENIED    = 17;
    const E_FETCH_UNKNOWN           = 30;
    const E_ARCHIVE_UNKNOWN         = 31;

    public function __construct($code = 0, $message = "") {
        parent::__construct($message, $code);
    }

    public function setOffendingFile($file) {
        $this->file = $file;
    }

    public function getOffendingFile() {
        return $this->file;
    }
}
