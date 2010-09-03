$_pfw_modules = array('mymodule');

$_pfw_routes = array(
    'mymodule_route' => array(
        '/foo/:controller/:action/:id/*', // route pattern
        array('module' => 'mymodule', 'action' => 'index', 'controller' => 'home', 'id' => null)
    )
);