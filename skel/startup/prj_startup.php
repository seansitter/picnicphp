<?php

/**
 * Add a project specific startup here
 */

global $_PATHS, $_ENVIRONMENT;

Pfw_Loader::loadFile("pfw_routes.php", $_PATHS['conf']);
Pfw_Loader::loadClass('Pfw_Controller_Front');
Pfw_Loader::loadClass('Pfw_PluginManager');
Pfw_Loader::loadClass('Pfw_Session');
Pfw_Loader::loadClass('Pfw_Alert');

// initialize the session
Pfw_Session::start();
// initialize the plugin manager
Pfw_PluginManager::init();
// initialize alerts
Pfw_Alert::init();

// turn off error display for production
if($_ENVIRONMENT == "production"){
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// setup front controller and routing
$front = Pfw_Controller_Front::getInstance();
$front->getRouter()->setRoutes($_pfw_routes)->setModules($_pfw_modules);
$four_oh_four = false;

try{
    $front->dispatch();
}
catch (Pfw_Exception_System $e) {
    $e->emitLog();
    if ($_ENVIRONMENT == "development") {
        objp($e); exit();
    }
    $four_oh_four = true;
}
catch (Pfw_Exception_User $e) {
    $e->emitLog();
    if ($_ENVIRONMENT == "development") {
        objp($e); exit();
    }
    $four_oh_four = true;
}
catch(Exception $e){
    if($_ENVIRONMENT == "development") {
        objp($e); exit();
    }
    $four_oh_four = true;
}

if($four_oh_four){
    Pfw_Loader::loadController('ErrorController');
    $c = new ErrorController();
    $c->fourohfourAction();
}

?>