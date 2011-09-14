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
    protected static $_pzListener;

    public function __construct(MongoCollection $collection, Array $doc = array())
    {
        $this->_pzCollection = $collection;
        $this->_pzCurrent = $doc;
        $this->_pzDoc     = $doc;
    }

    // getArray() {{{
    /**
     *  Return the current array version of the document
     *
     *  @return array
     */
    public function getArray()
    {
        return $this->_pzCurrent;
    }
    // }}}

    /* ArrayAccess {{{ */
    public function offsetExists($index) 
    {
        return array_key_exists($index, $this->_pzCurrent);
    }

    public function offsetGet($index) 
    {
        if (!array_key_exists($index, $this->_pzCurrent)) {
            return NULL;
        }
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

    public function __isset($index) {
        return array_key_exists($index, $this->_pzCurrent);
    }

    public function __set($index, $value) {
        return $this->offsetSet($index, $value);
    }
    // }}}

    // save($safe $fsync) {{{
    /**
     *  safe($safe=false, $fsync=false)
     *  
     *  safe the current document in the database, internally
     *  an insert or update is peformed
     *
     *  @param bool $safe
     *  @param bool $fsync
     *
     *  @return $this
     */
    public function save($safe=false, $fsync=false)
    {
        $current = $this->_pzCurrent;

        $args  = array('document' => &$current, 'collection' => $this->_pzCollection);
        $pzCol = $this->_pzCollection->getName();

        // trigger events 
        self::$_pzListener->trigger('preSave.' . $pzCol, $args);
        self::$_pzListener->trigger('preSave', $args);

        if (empty($this->_pzDoc)) {
            // insert, easy ;-)
            // trigger events 
            self::$_pzListener->trigger('preInsert.' . $pzCol, $args);
            self::$_pzListener->trigger('preInsert', $args);
            if (!isset($current['_id'])) {
                $current['_id'] = new \MongoId();
            }
            $this->_pzCollection->insert($current);
        } else {
            // updates, a bit tricky
            $document = array();
            $this->_getDocumentTosave($document, $this->_pzDoc, $current);
            if (empty($document)) {
                return $this;
            }

            // trigger events 
            $args['document'] = &$document;
            self::$_pzListener->trigger('preUpdate.' . $pzCol, $args);
            self::$_pzListener->trigger('preUpdate', $args);

            $criteria = array('_id' => $this->_pzDoc['_id']);
            if (isset($document['$pull'])) {
                /**
                 * $pulls with $set and $unset are invalid,
                 * so to make things easier we perform two 
                 * updates
                 */
                foreach ($document['$pull'] as $field => $pulls) {
                    foreach($pulls as $pull) {
                        $tmp = array('$pull' => array($field => $pull));
                        $this->_pzCollection->update(
                            $criteria,
                            $tmp,
                            compact('safe', 'fsync')
                        );
                    }
                }
                unset($document['$pull']);
            }

            $this->_pzCollection->update($criteria, $document, compact('safe', 'fsync'));
        }
        $this->_pzDoc = $current;

        return $this;
    }
    // }}}

    // _compareArrayTypes($arr1, $arr2) {{{
    /**
     *  Check if a two sub documents are objects or arrays
     *
     *  @return bool
     */
    protected function _compareArrayTypes($arr1, $arr2)
    {
        $isArray1 = array_keys($arr1) === range(0, count($arr1) -1);
        $isArray2 = array_keys($arr2) === range(0, count($arr2) -1);

        return $isArray1 == $isArray2;
    }
    // }}} 

    // _getDocumentTosave(Array, Array, Array, null) {{{
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
    protected function _getDocumentTosave(Array &$document, Array $original, Array $current, $namespace = null)
    {
        $zProp = array_keys($current);
        $pProp = array_keys($original);

        foreach ($zProp as $prop) {
            if ($namespace) {
                $name = "{$namespace}.{$prop}";
            } else {
                $name = $prop;
            }

            if (!array_key_exists($prop, $original) || $original[$prop] !== $current[$prop]) {
                if ($name == '_id') {
                    throw new \MongoException("Modify on _id not allowed on update");
                }
                if (is_scalar($current[$prop]) || !isset($original[$prop]) || is_scalar($original[$prop])) {
                    $document['$set'][$name] = $current[$prop];
                } else {
                    if (!$this->_compareArrayTypes($original[$prop], $current[$prop])) {
                        $document['$set'][$name] = $current[$prop];
                    } else {
                        $this->_getDocumentTosave($document, $original[$prop], $current[$prop], $name);
                    }
                }
            }
        }

        foreach ($pProp as $prop) {
            if (!array_key_exists($prop, $current)) {
                if ($namespace) {
                    if (is_numeric($prop)) {
                        $document['$pull'][$namespace][] = $original[$prop];
                    } else {
                        $document['$unset'][$namespace . "." . $prop] = 1;
                    }
                } else {
                    $document['$unset'][$prop] = 1;
                }
            }
        }

        foreach ($document as $key => $value) {
            if (!isset($value) && !is_null($value)) {
                unset($document[$key]);
            }
        }
    }
    // }}}

    public static function Listener() {
        if (self::$_pzListener === null) {
            self::$_pzListener = new Event;
        }
        return self::$_pzListener;
    }

}

MongoDocument::Listener();

