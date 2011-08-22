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

class MongoDocument implements \ArrayAccess
{
    protected $_pzCollection;
    protected $_pzCurrent;
    protected $_pzDoc;

    public function __construct(MongoCollection $collection, Array $doc = array())
    {
        $this->_pzCollection = $collection;
        $this->_pzCurrent = $doc;
        $this->_pzDoc     = $doc;
    }

    /* ArrayAccess {{{ */
    public function offsetExists($index) 
    {
        return isset($this->_pzCurrent[$index]);
    }

    public function offsetGet($index) 
    {
        return $this->_pzCurrent[$index];
    }

    public function offsetSet($index, $value) 
    {
        $this->_pzCurrent[$index] = $value;
    }

    public function offsetUnset($index)
    {
        unset($this->_pzCurrent[$index]);
    }
    // }}}

    // __get/__set {{{
    public function __get($index) {
        return $this->offsetGet($index);
    }

    public function __set($index, $value) {
        return $this->offsetSet($index, $value);
    }
    // }}}

    public function save($save=false, $fsync=false)
    {
        $current = $this->_pzCurrent;
        if (empty($this->_pzDoc)) {
            // insert, easy ;-)
            $this->_pzCollection->save($current);
        } else {
            // updates, a bit tricky
            $document = array();
            $this->_getDocumentToSave($document, $this->_pzDoc, $current);
            $criteria = array('_id' => $this->_pzDoc['_id']);
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
        $this->_pzDoc = $current;
    }

    // _getDocumentToSave(Array, Array, Array, null) {{{
    /**
     *  Perform a diff between the original document and the current one
     *  and return an update-document to perform in the database.
     *
     *
     *  @param array  &$document Query document
     *  @param array  $original  Original document
     *  @param array  $current   Current document
     *  @param string $namespace Namespace in case of comparing arrays or subdocuments.
     *
     *
     *  @return array
     */
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
    // }}}

}

