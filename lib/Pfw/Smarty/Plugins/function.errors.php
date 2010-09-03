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

function smarty_function_errors($params, &$smarty)
{
    list($args, $attrs) = $smarty->_filterAttrs($params);
    if (isset($args['for'])) {
        $obj = $smarty->get_template_vars($args['for']);
    }
    else {
        $obj = $args['on'];
    }
    if (!is_object($obj)) {
        $name = isset($args['for']) ? ": '{$args['for']}'" : "";
        error_log("invalid template object{$name}");
        return null;
    }
    $obj_prop = isset($args['prop']) ? $args['prop'] : null;
    
    if ($obj->hasErrors($obj_prop)) {  
        $errors = $obj->getErrors($obj_prop, true);  
        return $smarty->displayObjErrors($errors, $args, $attrs);
    }
    return null;
}
