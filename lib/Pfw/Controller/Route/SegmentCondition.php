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
 * Simple interface for segment condition
 *
 * If you need a more complex segment condition, you can implement interface,
 * and use it in your route conditions.
 * 
 * @category      Framework
 * @package       Pfw
 */
interface Pfw_Controller_Route_SegmentCondition {
    /**
     * Actually execute the segment condition
     *
     * @param string  The segment subject we're applying coniditions to
     * @return bool   Did the segment condition pass?
     */
    public function exec($subject);
}
