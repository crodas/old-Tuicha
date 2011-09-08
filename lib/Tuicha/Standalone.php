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
 *  Really simple class to manage multiple connections
 */
abstract class Standalone
{
    protected static $_db = array();
    protected $col;

    protected function setCollection($collection, $name='default')
    {
        if (empty(self::$_db[$name])) {
            throw new \Exception("Invalid connection name");
        }
        $this->col = self::$_db[$name]->$collection;
    }

    public static function Configure($db, $host="localhost", $name='default')
    {
        $conn = new Mongo($host, array('connect' => false));
        self::$_db[$name] = $conn->selectDB($db);
    }

    public function save($obj)
    {
        return $this->col->save($obj);
    }

    public function find($query = array(), $fields = array())
    {
        return $this->col->find($query, $fields);
    }

    public function findOne($query = array(), $fields = array())
    {
        return $this->col->findOne($query, $fields);
    }

    public function delete($query = array(), $options = array())
    {
        return $this->col->remove($query, $options);
    }

    public function remove($query = array(), $options = array())
    {
        return $this->col->remove($query, $options);
    }

}
