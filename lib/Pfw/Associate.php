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
 * Interface implemented by an association strategy.
 * 
 * This interface must be implemented by all association strategies.
 * The implementing class is specified when the association is defined
 * with a has* function in the model with the 'default_strategy' option, 
 * or at query time as an option to the with method.
 * 
 * @category      Framework
 * @package       Pfw
 * @see Pfw_QueryObject::with()
 * @see Pfw_Associate_PostQuery for an example
 */
interface Pfw_Associate
{
	/**
	 * Called by a query object on an implementing class
	 *
	 * @see Pfw_QueryObject::with()
	 * @see Pfw_Model::hasMany()
	 * @see Pfw_Model::hasOne()
	 * @see Pfw_Model::belongsTo()
	 * @param array &$objects
	 * @param string $association
	 * @param array $params
	 */
    public static function exec(&$objects, $association, $params = null);
}
