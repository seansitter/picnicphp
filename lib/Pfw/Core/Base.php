<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @category      Framework
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

/**
 * Prints data structure surrounded by '<pre>' tags.
 *
 * @param mixed $obj
 */
function objp($obj)
{
    echo '<pre class="pfw-objp">';
    print_r($obj);
    echo '</pre>';
}

/**
 * Determines if the given string would be a valid property name of a 
 * class.
 * 
 * @param string $prop
 * @return bool
 */
function is_valid_prop($prop)
{
    return (!empty($prop) and !preg_match('/^\0/', $prop)) ? true : false;
}

/**
 * Ensures that all elements of $subject are name => value pairs
 * If an element does not have a value, it is mapped to iteself.
 * 
 * @param array $subject
 * @return array
 */
function array_to_hash($subject)
{
    $rep = array();
    foreach ($subject as $key => $value) {
        if (is_int($key)){
            $key = $value;
        }
        $rep[$key] = $value;
    }
    return $rep;
}

/**
 * Select a random item from an array.
 * 
 * @param array $subject
 * @return mixed
 */
function rand_item($subject)
{
    if (!is_array($subject)) {
        return;
    }
    $rand_id = rand(0, count($subject) - 1);
    return $subject[$rand_id];
}

/**
 * Pluralizes a word.
 *
 * @param string $string
 * @return string
 */
function pluralize($string)
{
    $plural = array(
        array( '/(quiz)$/i',               "$1zes"   ),
        array( '/^(ox)$/i',                "$1en"    ),
        array( '/([m|l])ouse$/i',          "$1ice"   ),
        array( '/(matr|vert|ind)ix|ex$/i', "$1ices"  ),
        array( '/(x|ch|ss|sh)$/i',         "$1es"    ),
        array( '/([^aeiouy]|qu)y$/i',      "$1ies"   ),
        array( '/([^aeiouy]|qu)ies$/i',    "$1y"     ),
        array( '/(hive)$/i',               "$1s"     ),
        array( '/(?:([^f])fe|([lr])f)$/i', "$1$2ves" ),
        array( '/sis$/i',                  "ses"     ),
        array( '/([ti])um$/i',             "$1a"     ),
        array( '/(buffal|tomat)o$/i',      "$1oes"   ),
        array( '/(bu)s$/i',                "$1ses"   ),
        array( '/(alias|status)$/i',       "$1es"    ),
        array( '/(octop|vir)us$/i',        "$1i"     ),
        array( '/(ax|test)is$/i',          "$1es"    ),
        array( '/s$/i',                    "s"       ),
        array( '/$/',                      "s"       )
    );

    $irregular = array(
        array( 'move',   'moves'    ),
        array( 'sex',    'sexes'    ),
        array( 'child',  'children' ),
        array( 'man',    'men'      ),
        array( 'person', 'people'   )
    );

    $uncountable = array(
        'sheep',
        'fish',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    );

    // save some time in the case that singular and plural are the same
    if (in_array(strtolower($string), $uncountable)) {
        return $string;
    }

    // check for irregular singular forms
    foreach ($irregular as $noun) {
        if (strtolower( $string ) == $noun[0]) {
            return $noun[1];
        }
    }

    // check for matches using regular expressions
    foreach ($plural as $pattern) {
        if (preg_match($pattern[0], $string)) {
            return preg_replace( $pattern[0], $pattern[1], $string );
        }
    }

    return $string;
}

/**
 * Singularizes a word.
 * 
 * @param string $word
 * @return string
 */
function singularize($word)
{
    $singular = array (
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/([octop|vir])i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/s$/i' => '',
    );

    $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

    $irregular = array(
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves');

    $lowercased_word = strtolower($word);
    foreach ($uncountable as $_uncountable){
        if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
            return $word;
        }
    }

    foreach ($irregular as $_plural=> $_singular){
        if (preg_match('/('.$_singular.')$/i', $word, $arr)) {
            return preg_replace('/('.$_singular.')$/i', substr($arr[0],0,1).substr($_plural,1), $word);
        }
    }

    foreach ($singular as $rule => $replacement) {
        if (preg_match($rule, $word)) {
            return preg_replace($rule, $replacement, $word);
        }
    }

    return $word;
}

/**
 * Recursively merges two arrays, where duplicate items in the first array
 * are replaced with items in the second array.
 * 
 * @param array $array1
 * @param array $array2
 * @return array
 */
function array_merge_recursive_replace($array1, $array2)
{
    $arrays = func_get_args();
    $narrays = count($arrays);

    $ret = $arrays[0];

    for ($i = 1; $i < $narrays; $i ++) {
        foreach ($arrays[$i] as $key => $value) {
            if (is_array($value) && isset($ret[$key])) {
                $ret[$key] = array_merge_recursive_replace($ret[$key], $value);
            }
            else {
                $ret[$key] = $value;
            }
        }
    }

    return $ret;
}

/**
 * An alternative to db adapter specific character escaping, escapes strings
 * from user input to prevent sql injection.
 * 
 * @param string $subject
 * @param bool $show_quotes enclose the resulting string in ' quote marks
 * @return string
 */
function quote_smart($subject, $show_quotes = true)
{
    $qt_str = ($show_quotes) ? "'" : "";

    // quote if not a number or a numeric string
    if (!is_numeric($subject)) {
        if ($subject == "NULL") {
            return $subject;
        }
        // has the same function as mysql_real_escape_string
        $order   = array("\\", "'", "\"", "\x1a", "\x00", "\r", "\n");
        $replace = array('\\\\', "\'", "\\\"", "\\\x1a", "\\\x00", "\\r", "\\n");
        $subject = $qt_str . str_replace($order, $replace, $subject) . $qt_str;
        #$subject = $qt_str . mysql_real_escape_string($subject) . $qt_str;
    }
    return $subject;
}
