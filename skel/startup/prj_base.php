<?php

global $_picnic_path;
$curr_dir = dirname(__FILE__);

// include the environment file
require(dirname($curr_dir).'/conf/environment.php');
// include the paths file
require("{$curr_dir}/prj_paths.php");

// get our project lib file
$_prj_lib_path = rtrim(rtrim($_PATHS['lib'], '/'), "\\");

// begin by setting our project paths as highest priority
$_inc_path = $_prj_lib_path;
// add project third party paths
$_inc_path .= PATH_SEPARATOR . $_prj_lib_path . DIRECTORY_SEPARATOR . "ThirdParty";

// if picnic path is set explicitly, use it, otherwise we assume its already in the include path
if (isset($_picnic_path)) {
    // add picnic path
    $_inc_path .= PATH_SEPARATOR . $_picnic_path;
    // add picnic third party path
    $_inc_path .= PATH_SEPARATOR . $_picnic_path . DIRECTORY_SEPARATOR . "ThirdParty";
}

// setup project include path for our project
set_include_path($_inc_path . PATH_SEPARATOR . get_include_path());

require('Pfw/Startup/Base.php');
require("{$curr_dir}/prj_global_require.php");

// initialize the config
Pfw_Config::init();
date_default_timezone_set(Pfw_Config::get('default_timezone'));

?>