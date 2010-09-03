<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short description for file
 *
 * Long description for file (if any)...
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
Pfw_Loader::loadClass('Pfw_Smarty_Standard');

class Pfw_Smarty_Standard_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
    public function testGenEmptyElem()
    {
        $smarty = new Pfw_Smarty_Standard();
        $elem = $smarty->_generateElement(
          'script',
          array('type' => 'text/javascript'),
          null,
          array('empty' => true)
        );

        $this->assertEquals(
          rtrim("<script type=\"text/javascript\"></script>"),
          rtrim($elem)
        );
    }

    public function testGenClosedElem()
    {
        $smarty = new Pfw_Smarty_Standard();
        $elem = $smarty->_generateElement(
          'script',
          array('type' => 'text/javascript'),
          null,
          array('closed' => true)
        );
        $this->assertEquals(
          rtrim("<script type=\"text/javascript\"/>"),
          rtrim($elem)
        );
    }

    public function testGenOpenElem()
    {
        $smarty = new Pfw_Smarty_Standard();
        $elem = $smarty->_generateElement(
          'script',
          array('type' => 'text/javascript')
        );
        $this->assertEquals(
          rtrim("<script type=\"text/javascript\">"),
          rtrim($elem)
        );
    }

    public function testGenElemAttrs()
    {
        $smarty = new Pfw_Smarty_Standard();
        $elem = $smarty->_generateElement(
          'script',
          array(
            'type' => 'text/javascript',
            'foo'=> 'bar'
          )
        );
        $this->assertEquals(
          rtrim("<script type=\"text/javascript\" foo=\"bar\">"),
          rtrim($elem)
        );
    }

    public function testGetJsLinks()
    {
        global $_PATHS;
        $_PATHS['js'] = '/js/';

        $smarty = new Pfw_Smarty_Standard();
        $smarty->addJsLink('prototype.js');
        $link = $smarty->getJsLinksHtml();
        $this->assertEquals(
           rtrim("<script src=\"/js/prototype.js\" type=\"text/javascript\"></script>\n"),
           rtrim($link)
        );
    }

    public function testGetMultiJsLinks()
    {
        global $_PATHS;
        $_PATHS['js'] = '/js/';

        $smarty = new Pfw_Smarty_Standard();
        $this->setCnfLinkRev($smarty, 2);
        $smarty->addJsLink('prototype.js');
        $smarty->addJsLink('common', 'common.js');
        $link = $smarty->getJsLinksHtml();
        $this->assertEquals(
           rtrim("<script src=\"/js/prototype.v2.js\" type=\"text/javascript\"></script>\n".
           "<script src=\"/js/common.v2.js\" type=\"text/javascript\"></script>\n"),
           rtrim($link)
        );
    }


    public function testGetArrJsLinks()
    {
        global $_PATHS;
        $_PATHS['js'] = '/js/';

        $smarty = new Pfw_Smarty_Standard();
        $this->setCnfLinkRev($smarty, 2);
        $smarty->addJsLink('prototype.js');
        $smarty->addJsLink('common', array('file' => 'common.js'));
        $link = $smarty->getJsLinksHtml();
        $this->assertEquals(
           rtrim("<script src=\"/js/prototype.v2.js\" type=\"text/javascript\"></script>\n".
           "<script src=\"/js/common.v2.js\" type=\"text/javascript\"></script>\n"),
           rtrim($link)
        );
    }

    public function testGetArrJsLinks2()
    {
        global $_PATHS;
        $_PATHS['js'] = '/js/';

        $smarty = new Pfw_Smarty_Standard();
        $this->setCnfLinkRev($smarty, 2);
        $smarty->addJsLink('prototype.js');
        $smarty->addJsLink(
            'common', array('src' => 'http://www.example.com/js/common.js')
        );
        $link = $smarty->getJsLinksHtml();

        $this->assertEquals(
           rtrim("<script src=\"/js/prototype.v2.js\" type=\"text/javascript\"></script>\n".
           "<script src=\"http://www.example.com/js/common.js\" type=\"text/javascript\"></script>\n"),
           rtrim($link)
        );
    }

    public function testGetCssLinks()
    {
        global $_PATHS, $_SERVER;
        $_PATHS['css'] = '/css/';
        $_SERVER['HTTP_HOST'] = 'example.com';

        $smarty = new Pfw_Smarty_Standard();
        $smarty->addCssLink('common.css');
        $link = $smarty->getCssLinksHtml();
        $this->assertEquals(
           rtrim("<link href=\"http://example.com/css/common.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen, projection, tv\"></link>\n"),
           rtrim($link)
        );
    }

    public function testGetCssLinksArr()
    {
        global $_PATHS, $_SERVER;
        $_PATHS['css'] = '/css/';
        $_SERVER['HTTP_HOST'] = 'example.com';

        $smarty = new Pfw_Smarty_Standard();
        $smarty->addCssLink('common', array('file' => 'common.css', 'media' => 'screen'));
        $link = $smarty->getCssLinksHtml();
        $this->assertEquals(
           rtrim("<link href=\"/css/common.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\"></link>\n"),
           rtrim($link)
        );
    }

    public function testGetMultiCssLinks()
    {
        global $_PATHS, $_SERVER;
        $_PATHS['css'] = '/css/';
        $_SERVER['HTTP_HOST'] = 'example.com';

        $smarty = new Pfw_Smarty_Standard();
        $this->setCnfLinkRev($smarty, 2);
        $smarty->addCssLink('my.css');
        $smarty->addCssLink(
            'common',
            array('href' => 'http://www.testsite.com/common.css', 'media' => 'tv')
        );
        $link = $smarty->getCssLinksHtml();
        $this->assertEquals(
           rtrim("<link href=\"http://example.com/css/my.v2.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen, projection, tv\"></link>\n".
           "<link media=\"tv\" href=\"http://www.testsite.com/common.css\" rel=\"stylesheet\" type=\"text/css\"></link>\n"),
           rtrim($link)
        );
    }

    public function testGenerateLinkTo(){
        $pfw_routes = $this->getPfwRoutes();
        Pfw_Loader::loadClass('Pfw_Controller_Front');
        $front = Pfw_Controller_Front::getInstance();
        $front->getRouter()->setRoutes($pfw_routes);

        $smarty = new Pfw_Smarty_Standard();
        $params = array(
           'route' => 'default_action',
           'controller' => 'foo',
           'action' => 'bar'
        );
        $link = $smarty->_generateLinkTo($params, 'hello world');
        $this->assertEquals(
           rtrim('<a href="/foo/bar">hello world</a>'),
           rtrim($link)
        );
    }

    public function testGenerateShowLinkTo(){
        $pfw_routes = $this->getPfwRoutes();
        Pfw_Loader::loadClass('Pfw_Controller_Front');
        $front = Pfw_Controller_Front::getInstance();
        $front->getRouter()->setRoutes($pfw_routes);

        $smarty = new Pfw_Smarty_Standard();
        $params = array(
           'route' => 'default_show',
           'controller' => 'foo',
            'id' => '123',
           'action' => 'bar'
        );
        $link = $smarty->_generateLinkTo($params, 'hello world');
        $this->assertEquals(
           rtrim('<a href="/foo/123">hello world</a>'),
           rtrim($link)
        );
    }

    function setCnfLinkRev(&$smarty, $rev)
    {
        $smarty->_config[0]['vars']['LINKREL_REV'] = $rev;
    }

    function getPfwRoutes()
    {
        $_pfw_routes = array(
            'default_show' => array(
                '/:controller/:id',
                array('action' => 'show'),
                array('id' => '/^\d+$/')
           ),
           'default_id_action' => array(
                '/:controller/:action/:id'
           ),
           'default_action' => array(
                '/:controller/:action',
                array('action' => 'index', 'controller' => 'home')
           )
        );
        return $_pfw_routes;
    }
}

?>