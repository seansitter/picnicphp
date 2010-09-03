<?php

/**
 * This is the smarty class used in your project. Add in code here to extend it.
 */

Pfw_Loader::loadClass('Pfw_Smarty_Standard');

class Prj_Smarty_Standard extends Pfw_Smarty_Standard {    
    protected $my_js_links = array(
        'prototype' => 'prototype.js',
        'common' => 'common.js'
    );
    
    protected $my_css_links = array(
        'common' => 'common.css'
    );
    
    protected $my_vars = array();

    public function __construct()
    {
        $ret = parent::__construct();
        
        $config = Pfw_Config::getConfig();
        $this->assignSiteTitle($config['site_title']);

        $this->setupPaths();
        return $ret;
    }

    protected function setupPaths()
    {
        global $_PATHS;
        $this->template_dir = $_PATHS['app'] . '/views/';
        $this->js_dir = $_PATHS['htdocs'] . '/js/';
        $this->css_dir = $_PATHS['htdocs'] . '/css/';

        $this->compile_dir = $_PATHS['misc'] . '/runtime/templates_c/';
        $this->cache_dir = $_PATHS['misc'] . '/runtime/cache/';
        $this->config_dir = $_PATHS['conf'];
        $this->config_load('smarty.conf');
        array_push($this->plugins_dir, dirname(__FILE__)."/Plugins");
    }
}
