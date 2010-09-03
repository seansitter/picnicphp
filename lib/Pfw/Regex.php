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

Pfw_Loader::loadClass('Pfw_Controller_Route_SegmentCondition');

/**
 * A nice wrapper for procedural regular expressions
 *
 * Implements Pfw_Controller_Route_SegmentCondition as a convenience.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Regex implements Pfw_Controller_Route_SegmentCondition
{
    protected $pattern;
    protected $flags;
    protected $offset;
    protected $matches = array();

    public function __construct ($pattern, $flags=null, $offset=0) {
        $this->pattern = $pattern;
        $this->flags = $flags;
        $this->offset = $offset;
    }

    public function match($subject)
    {
        $this->resetMatches();
        $match = preg_match($this->pattern, $subject,
            $this->matches, $this->flags, $this->offset);

        if (false === $match) {
            trigger_error("Regex '$pattern' failed (likely malformed).",
                E_USER_WARNING);
        }
        return $match;
    }

    public function exec($subject)
    {
        $ret = $this->match($subject);
        if ($ret !== false and ($ret > 0)) {
            return true;
        }
        return false;
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function setPattern($pattern)
    {
        $this->resetMatches();
        $this->pattern = $pattern;
    }

    public function setFlags($flags)
    {
        $this->resetMatches();
        $this->flags = $flags;
    }

    public function setOffset($offset)
    {
        $this->resetMatches();
        $this->offset = $offset;
    }

    private function resetMatches()
    {
        $this->matches = array();
    }
}
