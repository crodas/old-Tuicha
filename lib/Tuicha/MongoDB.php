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

class MongoDB extends \MongoDB {
    public $conn;

    public function __construct(Mongo $conn, $name) {
        parent::__construct($conn, $name);
        $this->conn = $conn;
    }
    public function selectCollection($name) {
        return new MongoCollection($this, $name);
    }

    public function __get($name) {
        return $this->selectCollection($name);
    }

    public function createCollection($string, $capped = FALSE, $size = 0, $max = 0) {
    }
}

