<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Test for Pfw_Controller_Router_Standard
 *
 * PHP version 5
 *
 * Copyright 2008 The Picnic PHP Framework
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category      Framework
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2008 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 */

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');
Pfw_Loader::loadClass('Pfw_Controller_Router_Standard');

class Pfw_Controller_Router_Standard_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    function testBasic()
    {
        $pfw_routes = array(
            'basic_route' => array(
            '/:controller/:action',
            array('action' => 'index')
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);
        $route = $router->route('/home/stuff');
        $this->assertEquals(
            array('controller' => 'home', 'action' => 'stuff'),
            $route
        );
        $this->assertEquals('basic_route', $router->getSuccessRouteName());
     }

    function testBasicConds()
    {
        $pfw_routes = array(
            'basic_route' => array(
                '/:controller/:action/:var',
                null,
                array('var' => 'me')
        ));

        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);
        $route = $router->route('/home/stuff/bad');
        $this->assertTrue(empty($route));
        $route = $router->route('/home/stuff/me');
        $this->assertEquals(
            array('controller' => 'home', 'action' => 'stuff', 'var' => 'me'),
            $route
        );
    }

    function testArrayConds()
    {
        $pfw_routes = array(
            'basic_route' => array(
                '/:controller/:action/:var',
                null,
                array('var' => array('me', 'ok'))
        ));

        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        // should not match, var is not in enum
        $route = $router->route('/home/stuff/bad');
        $this->assertTrue(empty($route));

        // should match
        $route = $router->route('/home/stuff/me');
        $this->assertEquals(
            array('controller' => 'home', 'action' => 'stuff', 'var' => 'me'),
            $route
        );

        // should match
        $route = $router->route('/home/stuff/ok');
        $this->assertEquals(
            array('controller' => 'home', 'action' => 'stuff', 'var' => 'ok'),
            $route
        );
    }

    function testRegexpCondRoute()
    {
        $pfw_routes = array(
            'complex_route' => array(
                '/:controller/:action/:id',
                null,
                array('id' => '/^\d+$/')
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/blog/edit/a123');
        $this->assertTrue(empty($route));

        $route = $router->route('/blog/edit/123');
        $this->assertEquals(
            array('controller' => 'blog', 'action' => 'edit', 'id' => 123),
            $route
        );

        $pfw_routes = array(
            'complex_route' => array(
                '/:controller/:action/:id',
                null,
                array('id' => new Pfw_Regex('/\d+/'))
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);
        $route = $router->route('/blog/edit/a123');
        $this->assertEquals(
            array('controller' => 'blog', 'action' => 'edit', 'id' => 'a123'),
            $route
        );

        $pfw_routes = array(
            'complex_route' => array(
                '/:controller/:action/:id',
                null,
                array('id' => new Pfw_Regex('/\d+/'))
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);
        $route = $router->route('/blog/edit/a123');
        $this->assertEquals(
            array('controller' => 'blog', 'action' => 'edit', 'id' => 'a123'),
            $route
        );
    }

    function testDefaultFillins()
    {
        $pfw_routes = array(
            'default_index' => array(
            '/:controller/:action',
            array('controller' => 'home', 'action' => 'index')
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/');
        $this->assertEquals(
            array('controller' => 'home', 'action' => 'index'),
            $route
        );

        $route = $router->route('/home');
        $this->assertEquals(
            array('controller' => 'home', 'action' => 'index'),
            $route
        );
     }

     function testMissingDefaultFillins()
     {
        $pfw_routes = array(
            'default_index' => array(
            '/:controller/:action',
            array('controller' => 'home')
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/home');
        $this->assertTrue(empty($route));
     }

    function testWildcard()
    {
        $pfw_routes = array(
            'default_index' => array(
            '/:controller/:action/*'
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/blog/edit/123/');
        $this->assertEquals(
            array(
                'controller' => 'blog',
                'action' => 'edit',
                '0' => '123'
            ),
            $route
        );

        $route = $router->route('/blog/edit/123/more');
        $this->assertEquals(
            array(
                'controller' => 'blog',
                'action' => 'edit',
                '0' => '123',
                '1' => 'more'
            ),
            $route
        );
    }

    function testMissingWildcard()
    {
        $pfw_routes = array(
            'default_index' => array(
            '/:controller/:action'
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/blog/edit/123/more');
        $this->assertTrue(empty($route));
        $this->assertEquals(null, $router->getSuccessRouteName());
    }

    function testMultiRoute()
    {
        $pfw_routes = array(
            'id_action' => array(
                '/:controller/:action/:id',
                array('action' => 'index'),
                array('id' => '/^\d+$/')),
            'bag_lit_action' => array(
                '/:controller/a/:action/*'),
            'bag_action' => array(
                '/:controller/:action/*'),
            'default_action' => array(
                '/:controller/:action',
                array('action' => 'index'))
        );
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/blog/edit/123');
        $this->assertEquals('id_action', $router->getSuccessRouteName());

        $route = $router->route('/blog/edit/a123');
        $this->assertEquals('bag_action', $router->getSuccessRouteName());

        $route = $router->route('/blog/a/edit/a123');
        $this->assertEquals('bag_lit_action', $router->getSuccessRouteName());

        $route = $router->route('/blog');
        $this->assertEquals('default_action', $router->getSuccessRouteName());
     }

     function testNoneMatchLit()
     {
         $pfw_routes = array(
            'stuff' => array(
                '/:controller/a/:action/:id/*'
         ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/');
        $this->assertEquals(null, $router->getSuccessRouteName());
     }

     function testLiteralRoute()
     {
        $pfw_routes = array(
            'literal_route' => array(
            '/mine/yours',
            array('controller' => 'blog', 'action' => 'edit')
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/mine/yours');
        $this->assertEquals(array('controller' => 'blog', 'action' => 'edit'), $route);
        $route = $router->route('/mine/yours/');
        $this->assertEquals(array('controller' => 'blog', 'action' => 'edit'), $route);
     }

     function testComplexRoute()
     {
        $pfw_routes = array(
            'complex_route' => array(
                '/mine/:controller/a/yours/:action/:id',
                array('controller' => 'blog', 'action' => 'edit')
        ));
        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);

        $route = $router->route('/mine/blog/a/yours/edit/123');
        $this->assertEquals(
            array('controller' => 'blog', 'action' => 'edit', 'id' => 123),
            $route
        );
        $this->assertEquals('complex_route', $router->getSuccessRouteName());
    }

    function testMultiRegex()
    {
        $pfw_routes = array(
            'test_route_1' => array(
                '/:controller/:action/:id',
                array('controller' => 'blog'),
                array('id' => new Pfw_Regex('/^\d+$/'))
            ),
            'test_route_2' => array(
                '/:controller',
                array('controller' => 'blog'),
                array('id' => new Pfw_Regex('/\d+/'))
            )
        );

        $router = new Pfw_Controller_Router_Standard();
        $router->setRoutes($pfw_routes);
        $route = $router->route('/blog/edit/a123');
        $this->assertEquals(array(), $route);
        $this->assertEquals(null, $router->getSuccessRouteName());
     }
}

?>