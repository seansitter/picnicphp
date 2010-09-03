<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @category      Framework
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

// add the current directory to the top of the include path
$mydir = dirname(__FILE__);
$topdir = realpath($mydir.'/../../');
$old_inc_path = get_include_path();

global $_PATHS;
if (!isset($_PATHS['lib'])) {
    $_PATHS['lib'] = $topdir;
}

set_include_path(
    $mydir.
    PATH_SEPARATOR.
    $topdir.
    PATH_SEPARATOR.
    $old_inc_path
);

// include global requires
require('GlobalRequire.php');

global $_PICNIC_CLASSPATHS;
if (!$_PICNIC_CLASSPATHS) {
    // set the include path back
    set_include_path($old_inc_path);
}
