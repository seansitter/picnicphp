<?php

/**
 * Bootstraps bringing up MVC framework. Keep it simple.
 */

$_prj_startup_path = dirname(dirname(__FILE__)).'/startup';
require("{$_prj_startup_path}/prj_base.php");
require("{$_prj_startup_path}/prj_startup.php");

?>