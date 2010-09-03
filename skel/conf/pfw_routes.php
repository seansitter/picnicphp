<?php

// see: http://www.picnicphp.com/Pfw/tutorial_RequestRouting.pkg.html

global $_pfw_routes, $_pfw_modules;

$_pfw_modules = array();

$_pfw_routes = array(
'default_module_action' => array(
  '/:module/:controller/:action/:id/*',
  array('action' => 'index', 'controller' => 'home', 'id' => null)
),
'default_action' => array(
  '/:controller/:action/:id/*',
  array('action' => 'index', 'controller' => 'home', 'id' => null)
)
);

?>