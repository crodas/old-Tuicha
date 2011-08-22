<?php
/**
 * ----------------------------------------------------------------------------
 * Tuicha.
 *
 * <crodas@php.net> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return to César Rodas.
 * ----------------------------------------------------------------------------
 */
namespace Tuicha;

/**
 *  Simple function wrapping to avoid getting
 *  private/protected variables when this function 
 *  is called inside an object.
 *
 *  @return Array
 */
function get_object_vars($object) {
    return \get_object_vars($object);
}

class MongoDocument 
{
    protected $_pzCollection;
    protected $_pzCurrent;

    public function __construct(MongoCollection $collection, Array $doc = array()) {
        $this->_pzCollection = $collection;
        foreach ($doc as $prop => $val) {
            $this->$prop = $val;
        }
        $this->_pzCurrent = $doc;
    }

    public function save($save=false, $fsync=false) {
        $current = get_object_vars($this);
        if (empty($this->_pzCurrent)) {
            $this->_pzCollection->save($current);
            $this->_pzCurrent = $current;
        } else {
            $document = array();
            $this->_getDocumentToSave($document, $this->_pzCurrent, $current);
            $criteria = array('_id' => $this->_pzCurrent['_id']);
            if (isset($document['$pull'])) {
                /**
                 * $pulls with $set and $unset are invalid,
                 * so to make things easier we perform two 
                 * updates
                 */
                foreach ($document['$pull'] as $field => $pull) {
                    $tmp = array('$pull' => array($field => $pull));
                    $this->_pzCollection->update(
                        $criteria,
                        $tmp,
                        compact('safe', 'fsync')
                    );
                }
                unset($document['$pull']);
            }
            $this->_pzCollection->update($criteria, $document, compact('safe', 'fsync'));
        }
    }

    protected function _getDocumentToSave(Array &$document, Array $original, Array $current, $namespace = null)
    {
        $zProp = array_keys($current);
        $pProp = array_keys($original);

        foreach ($zProp as $prop) {
            if ($namespace) {
                $name = "{$namespace}.{$prop}";
            } else {
                $name = $prop;
            }
            if (!isset($original[$prop]) || $original[$prop] !== $current[$prop]) {
                if ($name == '_id') {
                    throw new ActiveMongo2_Exception("Mod on _id not allowed");
                }
                if (is_scalar($current[$prop]) || !isset($original[$prop])) {
                    $document['$set'][$name] = $current[$prop];
                } else {
                    $this->_getDocumentToSave($document, $original[$prop], $current[$prop], $name);
                }
            }
        }
    }
}

