<?php

require "../lib/Tuicha.php";

date_default_timezone_set('America/Asuncion');

$conn1 = new Tuicha\Mongo;
$conn2 = new Mongo;
$db1 = $conn1->tuicha;
$db2 = $conn2->tuicha;
