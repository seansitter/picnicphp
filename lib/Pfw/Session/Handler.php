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
interface Pfw_Session_Handler {
    public function open($save_path, $session_name);
    public function close();
    public function read($id);
    public function write($id, $session_data);
    public function destroy($id);
    public function gc($max_lifetime_s);
    public function permify($id);
    public function renew($id);
}

?>