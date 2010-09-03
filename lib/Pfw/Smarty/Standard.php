<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

global $_PATHS;
Pfw_Loader::loadFile('Smarty/Smarty.class.php');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Smarty_Standard extends Smarty
{
    protected $css_linkrel_arr = array();
    protected $ext_css_linkrel_arr = array();
    protected $css_attr_arr = array();
    protected $js_linkrel_arr = array();
    protected $ext_js_linkrel_arr = array();
    protected $js_attr_arr = array();
    protected $my_css_links = array();
    protected $my_js_links = array();
    protected $my_default_modifiers = array();
    protected $my_default_functions = array(
        'display_css_links' => 'smarty_function_display_css_links',
        'display_js_links' => 'smarty_function_display_js_links',
        'js_link' => 'smarty_function_js_link',
        'css_link' => 'smarty_function_css_link',
        'display_layout_body' => 'smarty_function_display_layout_body',
        'display_doctype' => 'smarty_function_display_doctype',
        'display_alerts' => 'smarty_function_display_alerts',
        'display_errors' => 'smarty_function_display_errors',
        'display_notices' => 'smarty_function_display_notices',
        'url_for' => 'smarty_function_url_for'
    );
    
    protected $my_default_block_functions = array(
        'link_to' => 'smarty_block_link_to'
    );

    protected $doctypes = array(
    'HTML5' =>
      "<!DOCTYPE html>",
    'XHTML11' =>
      "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">",
    'XHTML_BASIC1' =>
      "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML Basic 1.1//EN\" \"http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd\">",
    'XHTML1_STRICT' =>
      "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">",
    'XHTML1_TRANSITIONAL' =>
      "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">",
    'XHTML1_FRAMESET' =>
      "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">",
    'HTML4_STRICT' =>
      "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">",
    'HTML4_TRANSITIONAL' =>
      "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">",
    'HTML4_TRANSITIONAL_QUIRKS' =>
      "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">",
    'HTML4_FRAMESET' =>
      "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">"
    );

    const DEFAULT_DOCTYPE = 'XHTML1_STRICT';

    protected $layout_body_tpl;
    protected $default_layout = null;
    private static $instance = null;

    var $compiler_file = 'Pfw/Smarty/Compiler.php';
    var $compiler_class = 'Pfw_Smarty_Compiler';

    public function __construct(){
    	global $_PATHS;
        
        parent::__construct();        
        array_push($this->plugins_dir, dirname(__FILE__)."/Plugins");

        $this->assignDefaultVars();        
        $this->registerDefaultModifiers();
        $this->registerDefaultFunctions();
        $this->registerDefaultBlockFunctions();

        $this->registerCssLinks();
        $this->registerJsLinks();
    }

    public function getInstance(){
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setDefaultLayout($layout)
    {
        $this->default_layout = $layout;
    }
    
    public function clearDefaultLayout()
    {
        $this->default_layout = null;
    }
    
    public function getDefaultLayout()
    {
        return $this->default_layout;
    }
    
    public function display($args, $cache_id = null, $compile_id = null)
    {
    	return $this->_pfwFetch($args, $cache_id, $compile_id, true);
    }
    
    public function fetch($args, $cache_id = null, $compile_id = null)
    {
    	return $this->_pfwFetch($args, $cache_id, $compile_id, false);
    }

    protected function _pfwFetch($args, $cache_id, $compile_id, $display)
    {
        $default_layout = $this->getDefaultLayout();
        if (!is_array($args) and is_null($default_layout)) {
            return parent::fetch($args, $cache_id, $compile_id, $display);
        }
        elseif(is_array($args) and ($args['layout'] === false or $args['layout'] === null)) {
            return parent::fetch($args['body'], $cache_id, $compile_id, $display);
        }
        
        if (!is_array($args)) {
            $body = $args;
            $args = array();
            $args['body'] = $body;
        }
        
        $layout = null;
        if (isset($args['layout']) and !empty($args['layout'])) {
            $layout = $args['layout'];
        } elseif (!is_null($default_layout)) {
            $layout = $default_layout;
        }

        if (!is_null($layout)) {
            if (!isset($args['body']) or empty($args['body'])) {
                throw new Pfw_Exception_System("'body' argument was empty, nothing to display!");
            }
            $this->layout_body_tpl = $args['body'];
            return parent::fetch($layout, $cache_id, $compile_id, $display);
        }
        
        if (!isset($args['body']) or empty($args['body'])) {
            throw new Pfw_Exception_System("'body' argument was empty, nothing to display!");
        }
        
        return parent::fetch($args['body'], $cache_id, $compile_id, $display);
    }

    
    public function assign($tpl_var, $value)
    {
        parent::assign($tpl_var, $value);
        return $this;
    }


    /**
     * Register default variables
     */
    protected function assignDefaultVars()
    {
        if(!empty($this->my_default_vars)){
            foreach($this->my_default_vars as $name => $value){
                $this->assign($name, $value);
            }
        }
    }


    protected function registerDefaultFunctions()
    {
        if (!empty($this->my_default_functions)) {
            foreach ($this->my_default_functions as $alias => $fcn_name) {
                if (!function_exists($fcn_name)) {
                    trigger_error("Attempted to register undefined function " .
                         "'$fcn_name' as smarty modifier '$alias'.",
                         E_USER_WARNING);
                    continue;
                }
                $this->register_function($alias, $fcn_name);
            }
        }
    }


    protected function registerDefaultBlockFunctions()
    {
        if (!empty($this->my_default_block_functions)) {
            foreach ($this->my_default_block_functions as $alias => $fcn_name) {
                if (!function_exists($fcn_name)) {
                    trigger_error("Attempted to register undefined function " .
                         "'$fcn_name' as smarty modifier '$alias'.",
                         E_USER_WARNING);
                    continue;
                }
                $this->register_block($alias, $fcn_name);
            }
        }
    }


    protected function registerDefaultModifiers()
    {
        if (!empty($this->my_default_modifiers)) {
            foreach ($this->my_default_modifiers as $alias => $mod_name) {
                if (!function_exists($mod_name)) {
                    trigger_error("Attempted to register undefined function " .
                        "'$mod_name' as smarty modifier '$alias'.",
                        E_USER_WARNING);
                    continue;
                }
                $this->register_modifier($alias, $mod_name);
            }
        }
    }


    /**
     * Register default css links
     */
    protected function registerCssLinks()
    {
        $css_links = array_to_hash($this->my_css_links);

        foreach ($this->my_css_links as $alias => $link) {
            $this->addCssLink($alias, $link);
        }
    }


    /**
     * Register default js links
     */
    protected function registerJsLinks(){
        $js_linsk = array_to_hash($this->my_js_links);

        foreach ($this->my_js_links as $alias => $link) {
            $this->addJsLink($alias, $link);
        }
    }


    /**
     * Adds css links via alias => link in input array. External
     * links are not supported with this emthod
     * 
     * @param array $link_arr an array of alias => link pairs
     * @return $this
     */
    public function addCssLinks($link_arr)
    {
        $link_arr = array_to_hash($link_arr);

        foreach ($link_arr as $alias => $link) {
            $this->addCssLink($alias, $link);
        }
        return $this;
    }

    
    /**
     * Add a css link to the view, use {display_css_links} in the template
     * to render all assigned js links. First argument is an alias for
     * this link so that it can be accessed or deleted at a later time,
     * second argument is the filename. All javascript files should be in 
     * the htdocs/css directory and urls will be written with the /css/{$filename}
     * path. If the css link is external to the site, use the full url to the file
     * as the $link argument, and pass a boolean true as the third argument. 
     * 
     * @param string $alias
     * @param string $link if omitted, $alias is used as the $link
     * @param array $attrs attributes to pass through to the html element
     * @param boolean $external
     * @return $this
     */
    public function addCssLink($alias, $link = null, $attrs = array(), $external = false)
    {
    	if (is_null($link)) {
    		$link = $alias;
    	}
        
        if (true == $external) {
        	unset($this->css_linkrel_arr[$alias]);
            $this->ext_css_linkrel_arr[$alias] = $link;
        }
        else {
        	unset($this->ext_css_linkrel_arr[$alias]);
            $this->css_linkrel_arr[$alias] = $link;
        }
        
        $this->css_attr_arr[$alias] = $attrs;
        
        return $this;
    }

    /**
     * Gets the link associated with $alias
     * 
     * @param string $alias
     * @return string
     */
    public function getCssLink($alias){
    	if (isset($this->css_linkrel_arr[$alias])) {
    		return $this->css_linkrel_arr[$alias];
    	}
    	return $this->ext_css_linkrel_arr[$alias];
    }
    
    /**
     * Gets the element attributes associated with this link alias
     * 
     * @param string $alias
     * @return array
     */
    public function getCssLinkAttrs($alias){
        return $this->css_attr_arr[$alias];
    }

    /**
     * Gets the css alias => link array
     * 
     * @return array
     */
    public function getCssLinks(){
        return array_merge($this->ext_css_linkrel_arr, $this->css_linkrel_arr);
    }

    /**
     * Deletes the link associated with $alias
     * 
     * @param string $alias
     * @return $this
     */
    public function deleteCssLink($alias)
    {
        unset($this->css_linkrel_arr[$alias]);
        unset($this->ext_css_linkrel_arr[$alias]);
        unset($this->css_attr_arr[$alias]);
        return $this;
    }

    /**
     * Resets all css links and their attributes
     * 
     * @return $this
     */
    public function resetCssLinks()
    {
        $this->css_linkrel_arr = array();
        $this->ext_css_linkrel_arr = array();
        $this->css_attr_arr = array();
        return $this;
    }

    /**
     * Adds js links via alias => link in input array. External
     * links are not supported with this emthod
     * 
     * @param array $link_arr an array of alias => link pairs
     * @return $this
     */
    public function addJsLinks($link_arr)
    {
        $link_arr = array_to_hash($link_arrs);

        foreach ($link_arr as $alias => $link) {
            $this->addJsLink($alias, $link);
        }
        return $this;
    }

    /**
     * Add a js link to the view, use {display_js_links} in the template
     * to render all assigned js links. First argument is an alias for
     * this link so that it can be accessed or deleted at a later time,
     * second argument is the filename. All javascript files should be in 
     * the htdocs/js directory and urls will be written with the /js/{$filename}
     * path. If the js link is external to the site, use the full url to the file
     * as the $link argument, and pass a boolean true as the third argument.
     * 
     * @param string $alias
     * @param string $link z if omitted, $alias is used as the $link
     * @param array $attrs attributes to pass through to the html element
     * @param boolean $external
     * @return $this
     */
    public function addJsLink($alias, $link = null, $attrs = array(), $external = false)
    {
        if (is_null($link)) {
            $link = $alias;
        }
        
        if (true == $external) {
            unset($this->js_linkrel_arr[$alias]);
            $this->ext_js_linkrel_arr[$alias] = $link;
        }
        else {
            unset($this->ext_js_linkrel_arr[$alias]);
            $this->js_linkrel_arr[$alias] = $link;
        }
        
        $this->js_attr_arr[$alias] = $attrs;        
        
        return $this;
    }

    /**
     * Gets the link associated with $alias
     * 
     * @param string $alias
     * @return string
     */    
    public function getJsLink($alias)
    {
        if (isset($this->js_linkrel_arr[$alias])) {
            return $this->js_linkrel_arr[$alias];
        }
        return $this->ext_js_linkrel_arr[$alias];
    }
    
    /**
     * Gets the element attributes associated with this link alias
     * 
     * @param string $alias
     * @return array
     */
    public function getJsLinkAttrs($alias){
    	return $this->js_attr_arr[$alias];
    }

    /**
     * Gets the css alias => link array
     * 
     * @return array
     */
    public function getJsLinks()
    {
        return array_merge($this->ext_js_linkrel_arr, $this->js_linkrel_arr);
    }

    /**
     * Delete the link associated with $alias
     * 
     * @param string $alias
     * @return $this
     */
    public function deleteJsLink($alias)
    {
        unset($this->js_linkrel_arr[$alias]);
        unset($this->ext_js_linkrel_arr[$alias]);
        unset($this->css_attr_arr[$alias]);
        return $this;
    }

    /**
     * Resets all js links and their attributes
     * 
     * @return $this
     */
    public function resetJsLinks()
    {
        $this->js_linkrel_arr = array();
        $this->ext_js_linkrel_arr = array();
        $this->css_attr_arr = array();
        return $this;
    }

    /**
     * Assigns the page title, ie: the <title> head element
     * 
     * @param string $title
     * @return $this
     */
    public function assignPageTitle($title)
    {
        $this->assign('page_title', $title);
        return $this;
    }

    /**
     * Assigns the site title
     * 
     * @param string $title
     * @return $this
     */
    public function assignSiteTitle($title){
        $this->assign('site_title', $title);
        return $this;
    }


    /**
     * Generates an html element
     *
     * @param string   $name          The element name
     * @param array    $attributes    The element attributes
     * @param array    $options       Options
     * @return string                 The html element
     */
    public function _generateElement($name, $attributes = array(), $content = null, $options = array())
    {
        if (empty($name)) {
            return "";
        }

        $name = urlencode($name);
        $elem_str = "<".$name;
        $attributes = array_to_hash($attributes);

        if (!empty($attributes)) {
            foreach ($attributes as $attr_name => $attr_value) {
                $attr_value = $attr_value;
                $elem_str .= " {$attr_name}=\"{$attr_value}\"";
            }
        }

        if (isset($options['empty']) and $options['empty']) {
            $elem_str .= "></{$name}>";
        } elseif($content !== null) {
            $elem_str .= ">{$content}</{$name}>";
        } else {
            $closed = isset($options['closed']) ? $options['closed'] : false;
            if($closed){
                $elem_str .= "/>";
            } else{
                $elem_str .= ">";
            }
        }

        return $elem_str;
    }


    public function getCssLinksHtml()
    {
        if (empty($this->css_linkrel_arr) and empty($this->ext_css_linkrel_arr)) {
            return;
        }
 
        global $_PATHS;

        $css_linkrels = array_merge($this->ext_css_linkrel_arr, $this->css_linkrel_arr);
        $rev = $this->get_config_vars('LINKREL_REV');

        $links = "";
        foreach ($css_linkrels as $alias => $css_linkrel) {
            $attr = $this->css_attr_arr[$alias];
            if (isset($this->ext_css_linkrel_arr[$alias])) {
                $attr['href'] = $css_linkrel;
            } else {
                $attr['href'] = $_PATHS['css'].$css_linkrel;
                $attr['href'] = 'http://'.$_SERVER['HTTP_HOST'].$attr['href'];

                if (!empty($rev)) {
                    $attr['href'] = str_replace('.css', '', $attr['href']);
                    $attr['href'] = "{$attr['href']}.v{$rev}.css";
                }   
            }

            $links .= $this->_generateCssElement($attr)."\n";
        }

        return $links;
    }


    public function _generateCssElement($attrs)
    {
    	$fixed_attrs = array(
    	   'rel' => 'stylesheet',
    	   'type' => 'text/css',
    	);
        $attrs = array_merge($attrs, $fixed_attrs);
    	
        if (!isset($attrs['media'])) {
            $attrs['media'] = 'screen, projection, tv';
        }
        return $this->_generateElement(
            'link',
            $attrs,
            null,
            array('closed' => true)
        );
    }


    public function _handleCssLinkArr($css_linkrel){
        global $_PATHS;
        $attr = array();
        $rev = $this->get_config_vars('LINKREL_REV');

        if (isset($css_linkrel['src'])) {
            $css_linkrel['href'] = $css_linkrel['src'];
            unset($css_linkrel['src']);
        }

        if (isset($css_linkrel['href'])) {
            $attr['href'] = $css_linkrel['href'];
        } elseif (isset($css_linkrel['file'])) {
            $attr['href'] = $_PATHS['css'].$css_linkrel['file'];
        } else {
            trigger_error("In array format, one of 'file', 'href', or 'src' ".
                         "attributes are required");
        }

        if (isset($css_linkrel['file'])) {
            $inc_rev = isset($css_linkrel['inc_rev']) ? intval($css_linkrel['inc_rev']) : 1;
        } else {
            $inc_rev = 0;
        }
        if ($inc_rev and !empty($rev)) {
            $attr['href'] = str_replace('.js', '', $attr['href']);
            $attr['href'] = "{$attr['href']}.v{$rev}.js";
        }

        unset($css_linkrel['type']);
        unset($css_linkrel['rel']);
        unset($css_linkrel['file']);
        unset($css_linkrel['src']);

        $attr = array_reverse(array_merge($css_linkrel, $attr));
        return $attr;
    }


    public function getJsLinksHtml()
    {
        if (empty($this->js_linkrel_arr)) {
            return;
        }

        global $_PATHS;

        $js_linkrels = array_unique(array_to_hash($this->js_linkrel_arr));
        $rev = $this->get_config_vars('LINKREL_REV');

        $links = "";
        foreach ($js_linkrels as $alias => $js_linkrel) {
        	$attr = $this->js_attr_arr[$alias];
        	if (isset($this->ext_js_linkrels[$alias])) {
        		$attr['src'] = $js_linkrel;
        	} else {
        		$attr['src'] = $_PATHS['js'].$js_linkrel;

        		if (!empty($rev)) {
        			$attr['src'] = str_replace('.js', '', $attr['src']);
        			$attr['src'] = "{$attr['src']}.v{$rev}.js";
        		}
        	}

            $links .= $this->_generateJsElement($attr)."\n";
        }

        return $links;
    }


    public function _generateJsElement($params)
    {
    	$fixed_params = array(
    	   'type' => "text/javascript"
    	);
        $params = array_merge($params, $fixed_params);
        
        return $this->_generateElement(
           'script',
           $params,
           null,
           array('empty' => true)
        );
    }


    public function _handleJsLinkArr($js_linkrel){
        global $_PATHS;
        $attr = array();
        $rev = $this->get_config_vars('LINKREL_REV');

        if (isset($js_linkrel['src'])) {
            $attr['src'] = $js_linkrel['src'];
        } elseif (isset($js_linkrel['file'])) {
            $attr['src'] = $_PATHS['js'].$js_linkrel['file'];
        } else {
            trigger_error("In array format, one of 'file' or 'src' ".
                         "attributes are required");
        }

        if (isset($js_linkrel['file'])) {
            $inc_rev = isset($js_linkrel['inc_rev']) ? intval($js_linkrel['inc_rev']) : 1;
        } else {
            $inc_rev = 0;
        }
        if ($inc_rev and !empty($rev)) {
            $attr['src'] = str_replace('.js', '', $attr['src']);
            $attr['src'] = "{$attr['src']}.v{$rev}.js";
        }

        unset($js_linkrel['type']);
        unset($js_linkrel['file']);
        unset($js_linkrel['src']);

        $attr = array_merge($js_linkrel, $attr);
        return $attr;
    }
    
    public function _filterAttrs($params)
    {
        $attrs = array();
        $non_attrs = array();
        foreach ($params as $param => $value) {
            if ('attr.' == substr($param, 0, 5)) {
                $param = substr($param, 5);
                $attrs[$param] = $value;
            }
            else {
                $non_attrs[$param] = $value;
            }
        }
        return array($non_attrs, $attrs);
    }
    
    public function _generateUrlFor($params)
    {
        Pfw_Loader::loadClass('Pfw_Controller_Front');
        $front = Pfw_Controller_Front::getInstance();

        list($args, $attrs) = $this->_filterAttrs($params);
        if (!isset($args['route'])) {
            $args['route'] = 'default_action';
        }

        $router = $front->getRouter();
        $route = $args['route'];
        unset($args['route']);

        return $router->urlFor($route, $args);
    }

    public function _generateLinkTo($params, $content)
    {
        Pfw_Loader::loadClass('Pfw_Controller_Front');
        $front = Pfw_Controller_Front::getInstance();

        list($args, $attrs) = $this->_filterAttrs($params);

        if (!isset($attrs['href'])) {
            if (!isset($args['route'])) {
                $args['route'] = 'default_action';
            }
            
            $router = $front->getRouter();
            $route = $args['route'];
            unset($args['route']);
            
            $attrs['href'] = $router->urlFor($route, $args);
        }

        return $this->_generateElement('a', $attrs, $content);
    }
    

    public function getLayoutBody()
    {
        if (!empty($this->layout_body_tpl)) {
            return parent::fetch($this->layout_body_tpl, null, null, false);
        }
        return "";
    }


    public function getDoctype($type)
    {
        $type = strtoupper($type);
        if (!isset($this->doctypes[$type])) {
            trigger_error("'$type' is not a valid doctype. Using ".
                "'".self::DEFAULT_DOCTYPE."'");
            $type = self::DEFAULT_DOCTYPE;
        }
        return $this->doctypes[$type];
    }
    
    public function displayObjErrors($errors, $args, $attrs) {
        if (!isset($attrs['class'])) {
            $attrs['class'] = "pfw-obj-error";
        }
        $err_list = "\n";
        foreach ($errors as $err) {
            $err_list .= "<li>{$err}</li>\n";
        }
        return $this->_generateElement('ul', $attrs, $err_list);
    }
}

// Below are common plugins that are inlined

function smarty_function_display_alerts($args, &$smarty)
{
    if (Pfw_Alert::isInitialized()){
        $alerts = smarty_function_display_errors($args, $smarty);
        $alerts .= smarty_function_display_notices($args, $smarty);
        return $alerts;
    }
    return "";
}

function smarty_function_display_notices($args, &$smarty)
{
    $inc_fields = isset($args['inc_fields']) ? $args['inc_fields'] : false;
    if (Pfw_Alert::hasNotice()) {
        $notices = Pfw_Alert::getNotices($inc_fields);
        $list = "<ul class=\"pfw-alert pfw-notice\">\n";
        foreach ($notices as $notice) {
            $list .= "<li>{$notice}</li>\n";
        }
        $list .= "</ul>";
        return $list;
    }
    return "";
}

function smarty_function_display_errors($args, &$smarty)
{
    $inc_fields = isset($args['inc_fields']) ? $args['inc_fields'] : false;
    if (Pfw_Alert::hasError()) {
        $errors = Pfw_Alert::getErrors($in);
        $list = "<ul class=\"pfw-alert pfw-error\">\n";
        foreach ($errors as $error) {
            $list .= "<li>{$error}</li>\n";
        }
        $list .= "</ul>";
        return $list;
    }
    return "";
}

function smarty_function_display_css_links($args, &$smarty)
{
    return $smarty->getCssLinksHtml();
}

function smarty_function_display_js_links($args, &$smarty)
{
    return $smarty->getJsLinksHtml();
}

function smarty_function_js_link($args, &$smarty)
{
    $attrs = $smarty->_handleJsLinkArr($args);
    return $smarty->_generateJsElement($attrs);
}

function smarty_function_css_link($args, &$smarty)
{
    $attrs = $smarty->_handleCssLinkArr($args);
    return $smarty->_generateCssElement($attrs);
}

function smarty_function_url_for($args, &$smarty)
{
    return $smarty->_generateUrlFor($args, $content);

}

function smarty_function_display_doctype($args, &$smarty)
{
    return $smarty->getDoctype($args['type']);
}

function smarty_function_display_layout_body($args, &$smarty)
{
    return $smarty->getLayoutBody();
}

function smarty_block_link_to($args, $content, &$smarty, &$repeat)
{
    if(!$repeat) {
        return $smarty->_generateLinkTo($args, $content);
    }
}
