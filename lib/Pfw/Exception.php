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
 * The picnic wrapper for the builtin php exception class
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Exception extends Exception
{
    /**
     * Emits a log message indicating the exception with useful info
     *
     */
    public function emitLog()
    {
        $message = $this->getMessage();
        if (empty($message)) {
        	$message = get_class($this) . " exception caught";
        }
        $code = $this->getCode();
        if (empty($code)) {
            $code = "0";
        }
        $trace = $this->getTrace();
        $line = $trace[1]['line'];
        $file = $trace[1]['file'];
        trigger_error("EXCEPTION: {$message}. Originated on line: {$line} " .
            "in file: {$file} with code: {$code}", E_USER_WARNING);
    }
}
