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

class Mongo extends \Mongo  {
    public function selectDB($name) {
        return new MongoDB($this, $name);
    }

    public function __get($db) {
        return $this->selectDB($db);
    }
}
