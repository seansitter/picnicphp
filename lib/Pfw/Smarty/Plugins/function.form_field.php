<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @category      Framework
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 */

function smarty_function_form_field($params, &$smarty) 
{
    list($args, $attrs) = $smarty->_filterAttrs($params);
    
    $obj_prop = $args['prop'];
    if ($args['confirmation']) {
        $obj_prop = $obj_prop . "_confirm";
    }
    
    if (array_key_exists('value', $args)) {
        $attrs['value'] = $args['value'];
    }
    
    if (array_key_exists('index', $args)) {
        $args['for'] = $args['for']."[{$args['index']}]";
    }
    
    $obj_path = $args['for'];
    if (empty($obj_path)) {
        error_log("missing required template variable name 'for'");
    }
    
    // get the value of the property
    $var = "";
    if (false === strpos($obj_path, '[')) {
    	$var = $obj_path;
    	$obj = $smarty->get_template_vars($var);
    }
    else {
        preg_match("/^([\w]+)\[?/", $obj_path, $matches);
        $var = $matches[1];
    
        $obj_path = str_replace($var, 'obj', $obj_path);
        // not sure if this is needed
        $obj_path = preg_replace('/\[(?!\')/', '[\'', $obj_path);
        $obj_path = preg_replace('/\](?<!\')/', '\']', $obj_path);
        $e = "return \${$obj_path};";
    
        $obj = $smarty->get_template_vars($var);
        $obj = eval($e);
    }
    
    if (empty($obj)) {
    	Pfw_Loader::loadClass('Pfw_Form');
    	$obj = new Pfw_Form();
    }
    
    $element = "input";
    $content = null;
    
    if(!isset($args['type'])) {
        if (method_exists($obj, 'getSchema')) {
            $schema = $obj->getSchema();
            switch($schema[$obj_prop]['type'])
            {
                case Pfw_Db_Adapter::TYPE_CHARACTER:
                case Pfw_Db_Adapter::TYPE_INTEGER:
                case Pfw_Db_Adapter::TYPE_FLOAT:
                case Pfw_Db_Adapter::TYPE_ENUM:
                    $attrs['type'] = "text";
                    if (!isset($attrs['size']))
                        $attrs['size'] = 3;
                    break;
                case Pfw_Db_Adapter::TYPE_TEXT:
                    $attrs['type'] = "textarea";
                    break;
                case Pfw_Db_Adapter::TYPE_VARCHAR:
                    $attrs['type'] = "text";
                    if (!isset($attrs['size']))
                        $attrs['size'] = 32;
                    break;
                case Pfw_Db_Adapter::TYPE_DATE:
                    break;
                case Pfw_Db_Adapter::TYPE_TIME:
                    break;
            }
        }
        else {
            $attrs['type'] = "text";
        }
    }
    else {
        $attrs['type'] = $args['type'];
    }
    
    if (!isset($attrs['value']) and isset($obj->$obj_prop)) {
        if (is_object($obj)) {
        	$attrs['value'] = $obj->$obj_prop;
        }
        elseif(is_array($obj) and isset($obj[$obj_prop])) {
    	   $attrs['value'] = $obj[$obj_prop];
        }
    }
    
    if (!array_key_exists('value', $attrs)) {
        if(array_key_exists('default_value', $args)) {
            $attrs['value'] = $args['default_value'];
        }
        else {
            $attrs['value'] = "";
        }
    }
    
    if ($attrs['type'] == 'checkbox') {
        if ($attrs['value']) {
            $attrs['checked'] = "1";
        }
        $attrs['value'] = 1;
    }
    
    /*
    elseif (empty($attrs['value'])) {
        $attrs['value'] = "";
    }
    */
    
    if ('textarea' == $attrs['type']) {
        $element = "textarea";
        $content = $attrs['value'];
        unset($attrs['type']);
        unset($attrs['value']);
    }
    elseif ('select' == $attrs['type']) {
        $element = "select";
        $content = "\n";
        if (empty($params['options'])) {$params['options'] = array();}
        $options = array_to_hash($params['options']);
        foreach ($options as $label => $value) {
            $label = empty($label) ? $value : $label;
            $selected = "";
            if ($value == $attrs['value']) {
                $selected = " selected";
            }
            $value = urlencode($value);
            $content .= "<option value=\"{$value}\"{$selected}>$label</option>\n";
        }
        unset($attrs['type']);
        unset($attrs['value']);
    }
    
    $attrs['name'] = "{$args['for']}[$obj_prop]";
    
    $err_list = "";
    if (is_object($obj) and method_exists($obj, 'hasErrors') and $obj->hasErrors($args['prop']) and (false !== $args['highlight_errors'])) {
        if (isset($attrs['class'])) {
            $attrs['class'] = "{$attrs['class']} pfw-error";
        }
        else {
            $attrs['class'] = "pfw-error";
        }
    }
    
    $field = $smarty->_generateElement($element, $attrs, $content);
    
    return $err_list . $field;
}
