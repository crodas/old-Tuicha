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

class MongoCursor extends \MongoCursor {
    protected $col;

    public function __construct(MongoCollection $col, $query = array(), $fields = array()) {
        parent::__construct($col->db->conn, (string)$col, $query, $fields);
        $this->col = $col;
    }

    public function current() {
        $doc = parent::current();

        return new MongoDocument($this->col, $doc);
    }

}

