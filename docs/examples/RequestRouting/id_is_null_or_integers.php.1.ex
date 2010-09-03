$_pfw_routes = array(
    'default_action' => array(
        '/:controller/:action/:id/*', // route pattern
        array('controller' => 'home', 'action' => 'index', id => null), // route defaults
        array('id' => '/^\d+$/') // route conditions
    )
);