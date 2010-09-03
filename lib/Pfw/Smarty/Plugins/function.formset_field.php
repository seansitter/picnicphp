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

function smarty_function_formset_field($params, &$smarty) 
{
    list($args, $attrs) = $smarty->_filterAttrs($params);
    
    $var = $smarty->get_template_vars('_pfw_formset_var');
    $index = $smarty->get_template_vars('_pfw_formset_idx');
    $obj = $smarty->get_template_vars('_pfw_formset_obj');
    
    if (array_key_exists('value', $args)) {
    	$attrs['value'] = $args['value'];
    }
    
    $obj_prop = $args['prop'];
    if ($args['confirmation']) {
        $obj_prop = $obj_prop . "_confirm";
    }
    
    if (array_key_exists('index', $args)) {
        $args['for'] = $args['for']."[{$index}]";
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
    
    if (!isset($attrs['value'])) {
        if (is_object($obj) and isset($obj->$obj_prop)) {
            $attrs['value'] = $obj->$obj_prop;
        }
        elseif(is_array($obj) and isset($obj[$obj_prop])){
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
    
    $attrs['name'] = "{$var}[{$index}][$obj_prop]";
    
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
