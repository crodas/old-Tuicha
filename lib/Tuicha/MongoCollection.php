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

class MongoCollection extends \MongoCollection
{
    public function find($query = array(), $fields = array())
    {
        return new MongoCursor($this, $query, $fields);
    }

    public function findOneArray($query = array(), $fields = array())
    {
        return parent::findOne($query, $fields);
    }

    public function findOne($query = array(), $fields = array())
    {
        $doc = parent::findOne($query, $fields);
        if (is_array($doc)) {
            return new MongoDocument($this, $doc);
        } else {
            return NULL;
        }
    }

    public function create()
    {
        return new MongoDocument($this);
    }

    public function newDocument()
    {
        return $this->create();
    }
}

