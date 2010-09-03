<?php

require 'phpDocumentor/Converters/HTML/Smarty/HTMLSmartyConverter.inc';

/**
 * @package Converters
 * @subpackage HTMLPicnic
 */
class HTMLPicnicConverter extends HTMLSmartyConverter {
    var $outputformat = 'HTML';
    var $name = 'Picnic';

    function setTemplateDir($dir) {
        $templateBase = dirname(dirname(dirname(dirname(__FILE__))));
        $this->setTemplateBase($templateBase, $dir);
    }

    /**
     * Used to convert the <<code>> tag in a docblock
     * @param string
     * @param boolean
     * @return string
     */
    function ProgramExample($example, $tutorial = false, $inlinesourceparse = null/*false*/,
                            $class = null/*false*/, $linenum = null/*false*/, $filesourcepath = null/*false*/)
    {
        $trans = $this->template_options['desctranslate'];
        $this->template_options['desctranslate'] = array();
        $example = '<div class="example src-code"><ol>' . Converter::ProgramExample($example, $tutorial, $inlinesourceparse, $class, $linenum, 
$filesourcepath)
               .'</ol></div>';
        $this->template_options['desctranslate'] = $trans;
        if (!isset($this->template_options['desctranslate'])) return $example;
        if (!isset($this->template_options['desctranslate']['code'])) return $example;
        $example = $this->template_options['desctranslate']['code'] . $example;
        if (!isset($this->template_options['desctranslate']['/code'])) return $example;
        return $example . $this->template_options['desctranslate']['/code'];
    }
}
