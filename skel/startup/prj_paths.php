<?php

/**
 * This file sets up project paths. All paths should end with a trailing '/'.
 */

global $_PATHS;
if(!isset($_PATHS)){
  $_PATHS = array();
}

$ds = DIRECTORY_SEPARATOR;
// set up all the paths
$_PATHS['base'] = dirname(__FILE__) . "{$ds}..{$ds}";

// first setup app paths
$_PATHS['app']         = $_PATHS['base'] . "app{$ds}";
$_PATHS['lib']         = $_PATHS['app'] . "lib{$ds}";
$_PATHS['controllers'] = $_PATHS['app'] . "controllers{$ds}";
$_PATHS['models']      = $_PATHS['app'] . "models{$ds}";
$_PATHS['views']       = $_PATHS['app'] . "views{$ds}";
$_PATHS['startup']     = $_PATHS['app'] . "startup{$ds}";

// setup conf, misc path
$_PATHS['conf']        = $_PATHS['base'] . "conf{$ds}";
$_PATHS['misc']        = $_PATHS['base'] . "misc{$ds}";
$_PATHS['data']        = $_PATHS['base'] . "data{$ds}";
$_PATHS['script']      = $_PATHS['base'] . "script{$ds}";
$_PATHS['deltas']      = $_PATHS['data'] . "deltas{$ds}";
$_PATHS['htdocs']      = $_PATHS['base'] . "htdocs{$ds}";
$_PATHS['tmp']         = $_PATHS['misc'] . "tmp{$ds}";

$_PATHS['js']          = "/js/";
$_PATHS['css']         = "/css/";

?>