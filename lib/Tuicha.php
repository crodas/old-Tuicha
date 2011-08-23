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

if (!class_exists('Mongo')) {
    throw new \RuntimeException("You must install Mongo driver");
}

require __DIR__ . "/Tuicha/Mongo.php";
require __DIR__ . "/Tuicha/MongoDB.php";
require __DIR__ . "/Tuicha/MongoCollection.php";
require __DIR__ . "/Tuicha/MongoCursor.php";
require __DIR__ . "/Tuicha/Event.php";
require __DIR__ . "/Tuicha/MongoDocument.php";
