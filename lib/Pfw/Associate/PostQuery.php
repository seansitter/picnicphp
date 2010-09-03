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

Pfw_Loader::loadClass('Pfw_Associate');

/**
 * Implements an application join strategy base on the association of the
 * objects which will be association according to their association description.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Associate_PostQuery implements Pfw_Associate
{
    /**
     * Execute association strategy. Maps the associated objects into
     * each object in the array according to the object's association 
     * description. All object in the array must be instances of Pfw_Model,
     * and must be of the same type.
     * 
     * @param array &$objects the objects into wich the associations will be mapped
     * @param string $association the association to execute on the collection
     * @param $params extra options
     */
    public static function exec(&$objects, $association, $params = null)
    {
        if (empty($objects) or empty($association)) {
            return;
        }
        if (!is_array($objects)) {
            $objects = array($objects);
        }

        // find an owner object to sample
        $o = null;
        foreach ($objects as $sample) {
            if (!empty($sample)) {
                $o = $sample;
                break;
            }
        }
        if (empty($o)) {
            return;
        }

        $desc = $o->_getAssociationDescription($association);
        if (!isset($desc['thru'])) {
            self::execStandardMapping($association, $desc, $o, $objects);
        } else {
            self::execThruMapping($association, $desc, $o, $objects);
        }
    }

    protected function execStandardMapping($association, $desc, &$o, &$objects)
    {
        $count = $desc['count'];
        $class = $desc['class']; // the class of the thing we're associated with
        $fk = $desc['foreign_key'];
        $table = $desc['table'];

        $pks = array();
        foreach ($objects as &$object) {
            if (Pfw_Model::ASSOC_MANY == $count) {
                $object->$association = array();
                $my_key = $desc['my_key'];
                $pks[$object->$my_key] = $object;
            } elseif (Pfw_Model::ASSOC_ONE == $count) {
                $object->$association = null;
                $my_key = $desc['my_key'];
                $pks[$object->$my_key] = $object;
            } elseif (Pfw_Model::ASSOC_BELONGSTO == $count) {
                $object->$association = null;
                $pks[$object->$fk] = $object;
            }
        }

        Pfw_Loader::loadModel($class);
        $inst = new $class(); // empty instance of thing we're associated with
        $qo = $inst->Q($o->_getDb());

        if (isset($desc['conditions'])) {
            $join_cond = str_replace($association, 'this', $desc['conditions']);
            $qo->where($join_cond);
        }

        if ((Pfw_Model::ASSOC_MANY == $count) or (Pfw_Model::ASSOC_ONE == $count)){
            $associated_objs = $qo->whereIn("this.{$fk}", array_keys($pks))->exec();
        } elseif(Pfw_Model::ASSOC_BELONGSTO == $count) {
            $owner_key = $desc['owner_key'];
            $associated_objs = $qo->whereIn("this.{$owner_key}", array_keys($pks))->exec();
            $fk = $owner_key;
        }

        foreach ($associated_objs as $associated_obj) {
            $object = $pks[$associated_obj->$fk];
            if (empty($object)) {
                continue;
            }
            if (Pfw_Model::ASSOC_MANY == $count) {
                array_push($object->$association, $associated_obj);
            } elseif ((Pfw_Model::ASSOC_ONE == $count) or (Pfw_Model::ASSOC_BELONGSTO == $count)) {
                $object->$association = $associated_obj;
            }
        }
        unset($pks);
    }

    protected function execThruMapping($association, $desc, $o, $objects)
    {
        if ($desc['count'] == Pfw_Model::ASSOC_BELONGSTO) {
            throw new Pfw_Exception_Model("'thru' is not valid in belongsTo() associations");
        }

        $count = $desc['count'];
        $class = $desc['class']; // the class of the thing we're associated with
        $thru_class = $desc['thru_class'];
        $table = $desc['table'];

        $pks = array();
        foreach ($objects as &$object) {
            $my_key = $desc['my_key'];
            $pks[$object->$my_key] = $object;
            if (Pfw_Model::ASSOC_MANY == $count) {
                $object->$association = array();
            } elseif (Pfw_Model::ASSOC_ONE == $count) {
                $object->$association = null;
            }
        }

        $inst = new $class(); // empty instance of thing we're associated with
        $qo = $inst->Q($o->_getDb());

        if ((Pfw_Model::ASSOC_MANY == $count) or (Pfw_Model::ASSOC_ONE == $count)){
            // any conditions on the association table?
            if (isset($desc['conditions'])) {
                $join_cond = str_replace($association, 'this', $desc['conditions']);
                $qo->where($join_cond);
            }
            $qo->whereIn("{$desc['thru']}.{$desc['my_join_key']}", array_keys($pks));

            // any conditions on the association?
            $join_cond = "";
            if (isset($desc['conditions'])) {
                $join_cond = " AND {$desc['conditions']}";
            }

            $cond = "{$desc['thru']}.{$desc['as_join_key']} = this.{$desc['as_key']}";
            $qo->_getSelect()->joinLeft(
                array($desc['thru'] => $desc['thru_table']),
                array("{$cond}{$join_cond}"),
                self::getThruFields($desc['thru'], $desc['thru_fields'])
            );
            $associated_objs = $qo->exec();
        }

        foreach ($associated_objs as $associated_obj) {
            $thru = $desc['thru'];
            $thru_class = $desc['thru_class'];
            $my_join_key = $desc['my_join_key'];

            $thru_inst = new $thru_class($o->getClass(), $association);
            $thru_inst->setProperties($associated_obj->$thru);
            $associated_obj->$thru = $thru_inst;
            $object = $pks[$associated_obj->$thru->$my_join_key];
            if (empty($object)) {
                continue;
            }
            if (Pfw_Model::ASSOC_MANY == $count) {
                array_push($object->$association, $associated_obj);
            } elseif ((Pfw_Model::ASSOC_ONE == $count) or (Pfw_Model::ASSOC_BELONGSTO == $count)) {
                $object->$association = $associated_obj;
            }
        }

        unset($pks);
    }

    protected static function getThruFields($thru, $thru_fields)
    {
        // setup the select fields for the thru table
        $thru_fields = array_to_hash($thru_fields);
        $thru_select_fields = array();
        foreach ($thru_fields as $field) {
            $thru_select_fields["{$thru}.{$field}"] = $field;
        }
        return $thru_select_fields;
    }
}
