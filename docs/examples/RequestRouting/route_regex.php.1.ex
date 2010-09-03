$_pfw_routes = array(
    'default_action' => array(
        '/mystuff/:controller/:action/:id/*', // route pattern
        array('action' => 'index', 'controller' => 'home'), // route defaults
        array('id' => '/\d+/') // route conditions
    )
);