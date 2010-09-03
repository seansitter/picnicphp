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
 * Picnic plugin to remove magick quotes, if they are enabled
 *
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Plugin_UndoMagicQuotes
{
    public function preRoute()
    {
        if (get_magic_quotes_gpc()) {
            $in = array(&$_GET, &$_POST, &$_COOKIE);
            while (list($k,$v) = each($in)) {
                foreach ($v as $key => $val) {
                    if (!is_array($val)) {
                        $in[$k][$key] = stripslashes($val);
                        continue;
                    }
                    $in[] =& $in[$k][$key];
                }
            }
            unset($in);
        }
    }
}
