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

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_Plugin {
    public static function addEntry($entry_xml_file) {
        global $_PATHS;

        $plugins_xml_file = $_PATHS['conf']."/plugins.xml";
        $doc = DOMDocument::load($plugins_xml_file);
        $doc2 = DOMDocument::load($entry_xml_file);

        $plugins_top = &$doc->childNodes->item(0);
        $new_plugins_list = $doc2->childNodes->item(0)->childNodes;
        $new_len = $new_plugins_list->length;

        for($i = 1; $i < $new_len; $i++){
            $p = $new_plugins_list->item($i);
            $new_node = $doc->importNode($p, true);
            $plugins_top->appendChild($new_node);
        }
        $doc->save($plugins_xml_file);
    }

    public static function disable($name) {

    }

    public static function enable($name) {

    }

    public static function deleteEntry($name) {

    }
}
