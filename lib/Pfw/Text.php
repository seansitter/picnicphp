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
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Text {

    public static function sanitize($string)
    {
        if (is_array($string)) {
            return array_map("sanitize_text", $string);
        }
        # if mismatched < and > then convert to entities
        $lt_c = substr_count($string, "<");
        $gt_c = substr_count($string, ">");
        if ($lt_c != $gt_c) {
            $string = str_replace("<", "&lt;", $string);
            $string = str_replace(">", "&gt;", $string);
        }
        return trim(strip_tags(self::handleEntities(strval($string))));
    }

    public static function handleEntities($string, $keep_html = false)
    {
        // convert string to utf-8
        $string = self::makeUTF8($string);

        // convert everything to html entities
        if (self::isMbStringAvailable()) {
            $string = mb_convert_encoding($string, "HTML-ENTITIES", "UTF-8");
        }

        // mb_convert doesnt encode ampersands
        $string = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/m", "&#38;", $string);

        if (!$keep_html) {
            // convert html chars
            $string = str_replace(array("'", '"'), array('&#39;', '&#34;'), $string);
            $string = str_replace(array('<', '>'), array('&#60;', '&#62;'), $string);
        }
        
        return self::convertNumericEntities($string);
    }
    
    public static function removeEntities($string) {
        return preg_replace('/&[amp;]*\#\d+;/', '', $string);
    }

    public static function makeUTF8($string, $encoding = "") {
        $mb_string_functions = false;
        if (!self::isMbStringAvailable()) {
            $mb_string_functions = true;
        }
        
        if (empty($string)) {
            return $string;
        }

        if (empty($encoding) && self::isUTF8($string)) {
            $encoding = "UTF-8";
        }
        if (empty($encoding) and (true == $mb_string_functions)) {
            $encoding = mb_detect_encoding($string, 'UTF-8, ISO-8859-1');
        }
        if (empty($encoding)) {
            $encoding = "ISO-8859-1"; //  if charset can't be detected, default to ISO-8859-1
        }
        
        if (($encoding == "UTF-8") or (false == $mb_string_functions)) {
            return $string;
        }
        
        return @mb_convert_encoding($string, "UTF-8", $encoding);
    }
    
    /**
     * Much simpler UTF-8-ness checker using a regular expression created by the W3C:
     * Returns true if $string is valid UTF-8 and false otherwise.
     * From http://w3.org/International/questions/qa-forms-utf-8.html
     */
    public static function isUTF8($string) {
        return preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]           // ASCII
            | [\xC2-\xDF][\x80-\xBF]            // non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]        // excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} // straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]        // excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}     // planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}         // planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}     // plane 16
            )*$%xs', $string
        );
    }
    
    public static function isMbStringAvailable($warn = true) {
        if (!function_exists('mb_detect_encoding')) {
            if (true == $warn) {
                trigger_error("Multibyte string function are unavailable", E_USER_WARNING);
            }
            return false;
        }
        return true;
    }
    
    function convertNumericEntities($string) {
        $invalid_to_valid = array(
            '&#128;' => '&#8364;',
            '&#129;' => '',
            '&#130;' => '&#8218;',
            '&#131;' => '&#402;',
            '&#132;' => '&#8222;',
            '&#133;' => '&#8230;',
            '&#134;' => '&#8224;',
            '&#135;' => '&#8225;',
            '&#136;' => '&#710;',
            '&#137;' => '&#8240;',
            '&#138;' => '&#352;',
            '&#139;' => '&#8249;',
            '&#140;' => '&#338;',
            '&#141;' => '',
            '&#142;' => '&#382;',
            '&#143;' => '',
            '&#144;' => '',
            '&#145;' => '&#8216;',
            '&#146;' => '&#8217;',
            '&#147;' => '&#8220;',
            '&#148;' => '&#8221;',
            '&#149;' => '&#8226;',
            '&#150;' => '&#8211;',
            '&#151;' => '&#8212;',
            '&#152;' => '&#732;',
            '&#153;' => '&#8482;',
            '&#154;' => '&#353;',
            '&#155;' => '&#8250;',
            '&#156;' => '&#339;',
            '&#157;' => '',
            '&#158;' => '',
            '&#159;' => '&#376;'
        );
    
        // convert lone & character into &#38; (aka: &amp;)
        $string = preg_replace('/&([^#])(?![a-z1-4]{1,8};)/i', '&#038;$1', $string);
    
        // fix for copy and pasting from MS Word
        $string = strtr($string, $invalid_to_valid);
    
        // convert from words to nums
        $to_nums = array(
            '&quot;' => '&#34;',
            '&amp;' => '&#38;',
            '&frasl;' => '&#47;',
            '&lt;' => '&#60;',
            '&gt;' => '&#62;',
            '|' => '&#124;',
            '&nbsp;' => '&#160;',
            '&iexcl;' => '&#161;',
            '&cent;' => '&#162;',
            '&pound;' => '&#163;',
            '&curren;' => '&#164;',
            '&yen;' => '&#165;',
            '&brvbar;' => '&#166;',
            '&brkbar;' => '&#166;',
            '&sect;' => '&#167;',
            '&uml;' => '&#168;',
            '&die;' => '&#168;',
            '&copy;' => '&#169;',
            '&ordf;' => '&#170;',
            '&laquo;' => '&#171;',
            '&not;' => '&#172;',
            '&shy;' => '&#173;',
            '&reg;' => '&#174;',
            '&macr;' => '&#175;',
            '&hibar;' => '&#175;',
            '&deg;' => '&#176;',
            '&plusmn;' => '&#177;',
            '&sup2;' => '&#178;',
            '&sup3;' => '&#179;',
            '&acute;' => '&#180;',
            '&micro;' => '&#181;',
            '&para;' => '&#182;',
            '&middot;' => '&#183;',
            '&cedil;' => '&#184;',
            '&sup1;' => '&#185;',
            '&ordm;' => '&#186;',
            '&raquo;' => '&#187;',
            '&frac14;' => '&#188;',
            '&frac12;' => '&#189;',
            '&frac34;' => '&#190;',
            '&iquest;' => '&#191;',
            '&Agrave;' => '&#192;',
            '&Aacute;' => '&#193;',
            '&Acirc;' => '&#194;',
            '&Atilde;' => '&#195;',
            '&Auml;' => '&#196;',
            '&Aring;' => '&#197;',
            '&AElig;' => '&#198;',
            '&Ccedil;' => '&#199;',
            '&Egrave;' => '&#200;',
            '&Eacute;' => '&#201;',
            '&Ecirc;' => '&#202;',
            '&Euml;' => '&#203;',
            '&Igrave;' => '&#204;',
            '&Iacute;' => '&#205;',
            '&Icirc;' => '&#206;',
            '&Iuml;' => '&#207;',
            '&ETH;' => '&#208;',
            '&Ntilde;' => '&#209;',
            '&Ograve;' => '&#210;',
            '&Oacute;' => '&#211;',
            '&Ocirc;' => '&#212;',
            '&Otilde;' => '&#213;',
            '&Ouml;' => '&#214;',
            '&times;' => '&#215;',
            '&Oslash;' => '&#216;',
            '&Ugrave;' => '&#217;',
            '&Uacute;' => '&#218;',
            '&Ucirc;' => '&#219;',
            '&Uuml;' => '&#220;',
            '&Yacute;' => '&#221;',
            '&THORN;' => '&#222;',
            '&szlig;' => '&#223;',
            '&agrave;' => '&#224;',
            '&aacute;' => '&#225;',
            '&acirc;' => '&#226;',
            '&atilde;' => '&#227;',
            '&auml;' => '&#228;',
            '&aring;' => '&#229;',
            '&aelig;' => '&#230;',
            '&ccedil;' => '&#231;',
            '&egrave;' => '&#232;',
            '&eacute;' => '&#233;',
            '&ecirc;' => '&#234;',
            '&euml;' => '&#235;',
            '&igrave;' => '&#236;',
            '&iacute;' => '&#237;',
            '&icirc;' => '&#238;',
            '&iuml;' => '&#239;',
            '&eth;' => '&#240;',
            '&ntilde;' => '&#241;',
            '&ograve;' => '&#242;',
            '&oacute;' => '&#243;',
            '&ocirc;' => '&#244;',
            '&otilde;' => '&#245;',
            '&ouml;' => '&#246;',
            '&divide;' => '&#247;',
            '&oslash;' => '&#248;',
            '&ugrave;' => '&#249;',
            '&uacute;' => '&#250;',
            '&ucirc;' => '&#251;',
            '&uuml;' => '&#252;',
            '&yacute;' => '&#253;',
            '&thorn;' => '&#254;',
            '&yuml;' => '&#255;',
            '&OElig;' => '&#338;',
            '&oelig;' => '&#339;',
            '&Scaron;' => '&#352;',
            '&scaron;' => '&#353;',
            '&Yuml;' => '&#376;',
            '&fnof;' => '&#402;',
            '&circ;' => '&#710;',
            '&tilde;' => '&#732;',
            '&Alpha;' => '&#913;',  //Upper Greek
            '&Beta;' => '&#914;',
            '&Gamma;' => '&#915;',
            '&Delta;' => '&#916;',
            '&Epsilon;' => '&#917;',
            '&Zeta;' => '&#918;',
            '&Eta;' => '&#919;',
            '&Theta;' => '&#920;',
            '&Iota;' => '&#921;',
            '&Kappa;' => '&#922;',
            '&Lambda;' => '&#923;',
            '&Mu;' => '&#924;',
            '&Nu;' => '&#925;',
            '&Xi;' => '&#926;',
            '&Omicron;' => '&#927;',
            '&Pi;' => '&#928;',
            '&Rho;' => '&#929;',
            '&Sigma;' => '&#931;',
            '&Tau;' => '&#932;',
            '&Upsilon;' => '&#933;',
            '&Phi;' => '&#934;',
            '&Chi;' => '&#935;',
            '&Psi;' => '&#936;',
            '&Omega;' => '&#937;',
            '&alpha;' => '&#945;',  // lower greek
            '&beta;' => '&#946;',
            '&gamma;' => '&#947;',
            '&delta;' => '&#948;',
            '&epsilon;' => '&#949;',
            '&zeta;' => '&#950;',
            '&eta;' => '&#951;',
            '&theta;' => '&#952;',
            '&iota;' => '&#953;',
            '&kappa;' => '&#954;',
            '&lambda;' => '&#955;',
            '&mu;' => '&#956;',
            '&nu;' => '&#957;',
            '&xi;' => '&#958;',
            '&omicron;' => '&#959;',
            '&pi;' => '&#960;',
            '&rho;' => '&#961;',
            '&sigmaf;' => '&#962;',
            '&sigma;' => '&#963;',
            '&tau;' => '&#964;',
            '&upsilon;' => '&#965;',
            '&phi;' => '&#966;',
            '&chi;' => '&#967;',
            '&psi;' => '&#968;',
            '&omega;' => '&#969;',
            '&thetasym;' => '&#977;',
            '&upsih;' => '&#978;',
            '&piv;' => '&#982;',
            '&ensp;' => '&#8194;',
            '&emsp;' => '&#8195;',
            '&thinsp;' => '&#8201;',
            '&zwnj;' => '&#8204;',
            '&zwj;' => '&#8205;',
            '&lrm;' => '&#8206;',
            '&rlm;' => '&#8207;',
            '&ndash;' => '&#8211;',
            '&mdash;' => '&#8212;',
            '&lsquo;' => '&#8216;',
            '&rsquo;' => '&#8217;',
            '&sbquo;' => '&#8218;',
            '&ldquo;' => '&#8220;',
            '&rdquo;' => '&#8221;',
            '&bdquo;' => '&#8222;',
            '&dagger;' => '&#8224;',
            '&Dagger;' => '&#8225;',
            '&bull;' => '&#8226;',
            '&hellip;' => '&#8230;',
            '&permil;' => '&#8240;',
            '&prime;' => '&#8242;',
            '&Prime;' => '&#8243;',
            '&lsaquo;' => '&#8249;',
            '&rsaquo;' => '&#8250;',
            '&oline;' => '&#8254;',
            '&frasl;' => '&#8260;',
            '&euro;' => '&#8364;',
            '&image;' => '&#8465;',
            '&weierp;' => '&#8472;',
            '&real;' => '&#8476;',
            '&trade;' => '&#8482;',
            '&alefsym;' => '&#8501;',
            '&crarr;' => '&#8629;',
            '&lArr;' => '&#8656;',
            '&uArr;' => '&#8657;',
            '&rArr;' => '&#8658;',
            '&dArr;' => '&#8659;',
            '&hArr;' => '&#8660;',
            '&forall;' => '&#8704;',
            '&part;' => '&#8706;',
            '&exist;' => '&#8707;',
            '&empty;' => '&#8709;',
            '&nabla;' => '&#8711;',
            '&isin;' => '&#8712;',
            '&notin;' => '&#8713;',
            '&ni;' => '&#8715;',
            '&prod;' => '&#8719;',
            '&sum;' => '&#8721;',
            '&minus;' => '&#8722;',
            '&lowast;' => '&#8727;',
            '&radic;' => '&#8730;',
            '&prop;' => '&#8733;',
            '&infin;' => '&#8734;',
            '&ang;' => '&#8736;',
            '&and;' => '&#8743;',
            '&or;' => '&#8744;',
            '&cap;' => '&#8745;',
            '&cup;' => '&#8746;',
            '&int;' => '&#8747;',
            '&there4;' => '&#8756;',
            '&sim;' => '&#8764;',
            '&cong;' => '&#8773;',
            '&asymp;' => '&#8776;',
            '&ne;' => '&#8800;',
            '&equiv;' => '&#8801;',
            '&le;' => '&#8804;',
            '&ge;' => '&#8805;',
            '&sub;' => '&#8834;',
            '&sup;' => '&#8835;',
            '&nsub;' => '&#8836;',
            '&sube;' => '&#8838;',
            '&supe;' => '&#8839;',
            '&oplus;' => '&#8853;',
            '&otimes;' => '&#8855;',
            '&perp;' => '&#8869;',
            '&sdot;' => '&#8901;',
            '&lceil;' => '&#8968;',
            '&rceil;' => '&#8969;',
            '&lfloor;' => '&#8970;',
            '&rfloor;' => '&#8971;',
            '&lang;' => '&#9001;',
            '&rang;' => '&#9002;',
            '&larr;' => '&#8592;',
            '&uarr;' => '&#8593;',
            '&rarr;' => '&#8594;',
            '&darr;' => '&#8595;',
            '&harr;' => '&#8596;',
            '&loz;' => '&#9674;',
            '&spades;' => '&#9824;',  // cards
            '&clubs;' => '&#9827;',
            '&hearts;' => '&#9829;',
            '&diams;' => '&#9830;'
        );
    
        return str_replace(array_keys($to_nums), array_values($to_nums), $string);
    }
}
