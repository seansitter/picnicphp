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

Pfw_Loader::loadFile('Smarty/Smarty_Compiler.class.php');

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Smarty_Compiler extends Smarty_Compiler {
	
	function _compile_tag($template_tag)
    {
    	/* Split tag into two three parts: command, command modifiers and the arguments. */
        if(! preg_match('~^(?:(' . $this->_num_const_regexp . '|' . $this->_obj_call_regexp . '|' . $this->_var_regexp
                . '|\/?' . $this->_reg_obj_regexp . '|\/?' . $this->_func_regexp . ')(' . $this->_mod_regexp . '*))
                      (?:\s+(.*))?$
                    ~xs', $template_tag, $match)) {
            $this->_syntax_error("unrecognized tag: $template_tag", E_USER_ERROR, __FILE__, __LINE__);
        }
        
        $tag_command = $match[1];
        $tag_modifier = isset($match[2]) ? $match[2] : null;
        $tag_args = isset($match[3]) ? $match[3] : null;
	    
        switch ($tag_command) {
            case 'formset':
                $this->_push_tag('formset');
                return $this->_compile_formset_start($tag_args);

            case 'formsetelse':
                $this->_push_tag('formsetelse');
                return "<?php endforeach; else: ?>";

            case '/formset':
                $_open_tag = $this->_pop_tag('formset');
                if ($_open_tag == 'formsetelse')
                    return "<?php endif; unset(\$_from);?>";
                else
                    $output = "<?php unset(\$this->_tpl_vars['_pfw_formset_var']);\n";
                    $output .= "unset(\$this->_tpl_vars['_pfw_formset_obj']);\n";
                    $output .= "unset(\$this->_tpl_vars['_pfw_formset_idx']);\n";
                    $output .= "endforeach; endif; unset(\$_from); ?>";
                    return $output;
                break;
        }
        
        return parent::_compile_tag($template_tag);
    }
    
    function _compile_formset_start($tag_args)
    {
        $attrs = $this->_parse_attrs($tag_args);
        $arg_list = array();

        if (empty($attrs['for'])) {
            return $this->_syntax_error("formset: missing 'for' attribute", E_USER_ERROR, __FILE__, __LINE__);
        }
        $for = $this->_dequote($attrs['for']);
        if (!preg_match('~^\w+$~', $for)) {
            return $this->_syntax_error("formset: 'for' must be a variable name (literal string)", E_USER_ERROR, __FILE__, __LINE__);
        }
        if ($attrs['init']) {
        	$from = "empty(\$this->_tpl_vars[{$attrs['for']}]) ? array_fill(0, {$attrs['init']}, array()) : \$this->_tpl_vars[{$attrs['for']}]";
        } else {
            $from = "\$this->_tpl_vars[{$attrs['for']}]";
        }
        
        if (empty($attrs['item'])) {
        	$attrs['item'] = '_pfw_formset_obj';
        }
        $item = $this->_dequote($attrs['item']);
        if (!preg_match('~^\w+$~', $item)) {
            return $this->_syntax_error("foreach: 'item' must be a variable name (literal string)", E_USER_ERROR, __FILE__, __LINE__);
        }
        
        if (isset($attrs['index'])) {
            $index  = $this->_dequote($attrs['index']);
            if (!preg_match('~^\w+$~', $index)) {
                return $this->_syntax_error("foreach: 'key' must to be a variable name (literal string)", E_USER_ERROR, __FILE__, __LINE__);
            }
        } else {
            $index = '_pfw_formset_idx';
        }
        $index_part = "\$this->_tpl_vars['$index']";
        

        $output = '<?php ';
        $output .= "\$_from = $from; \n";
        $output .= "if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }";
        $output .= "if (count(\$_from)):\n";
        $output .= "    foreach (\$_from as $index_part => \$this->_tpl_vars['$item']):\n";
        $output .= "        \$this->_tpl_vars['_pfw_formset_idx'] = $index_part;\n";
        $output .= "        \$this->_tpl_vars['_pfw_formset_obj'] = \$this->_tpl_vars['$item'];\n";
        $output .= "        \$this->_tpl_vars['_pfw_formset_var'] = {$attrs['for']};\n";
        $output .= '?>';

        return $output;
    }
    
    function _pop_tag($close_tag) {
    	$message = '';
        if (count($this->_tag_stack)>0) {
            list($_open_tag, $_line_no) = array_pop($this->_tag_stack);
            if ($close_tag == $_open_tag) {
                return $_open_tag;
            }
            if ($close_tag == 'formset' && $_open_tag == 'formsetelse') {
                $this->_pop_tag($close_tag);
                return $_open_tag;
            }
            if ($_open_tag == 'formsetelse') {
                $_open_tag = 'formset';
                $message = " expected {/$_open_tag} (opened line $_line_no).";
                $this->_syntax_error("mismatched tag {/$close_tag}.$message",
                             E_USER_ERROR, __FILE__, __LINE__);
                return;
            }
        }
        return parent::_pop_tag($close_tag);
    }
}
