$_pfw_routes = array(
    'default_action' => array(
        '/mystuff/:controller/:action/*', // route definition
        array('action' => 'index', 'controller' => 'home'), // route defaults
        array('controller' => array('home', 'account', 'profile')) // route conditions
    )
);