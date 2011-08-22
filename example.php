<?php
require "lib/Tuicha.php";

// Create new Tuicha\Mongo
$conn = new Tuicha\Mongo;

// Everything looks the same
$db   = $conn->Database;
$col  = $db->someCollection;

// We introduce the MongoDocument
$doc = $col->newDocument();
$doc->foo = 1;
$doc->save();
$doc->foo = 2;
$doc->bar = 1;
$doc->save();

foreach ($conn->Database->someCollection->find() as $doc) {
    $doc->mod = time();
    $doc->save();
}

