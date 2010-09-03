$_pfw_routes = array(
    'default_module_action' => array(
        '/:module/:controller/:action/:id/*',
        array('action' => 'index', 'controller' => 'home', 'id' => null)
    )
);