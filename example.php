<?php
require "lib/Tuicha.php";

// Create new Tuicha\Mongo
$conn = new Tuicha\Mongo;
$conn->Database->drop();

// Everything looks the same
$conn->Database->drop();
$db   = $conn->Database;
$col  = $db->createCollection('someCollection');


// We introduce the MongoDocument Object (with ArrayAccess)
$doc = $col->newDocument();
$doc->foo = 1;
$doc->save();
$doc->foo = 2;
$doc->bar = 1;
$doc['arr'] = 3;
$doc->save();

foreach ($conn->Database->someCollection->find() as $doc) {
    $doc->mod = time(); // Object
    $doc['xx xx'] = time(); // Or Array
    $doc->save();
}

