<?php

header("Content-type: text/html; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once("Kijiji.php");

$id = $_GET['id'];
$c = new User();
$c->id = $id ? $id : 10000002;		//???number don't remember;
$c->load();

print "<h1><b>$c->name</b></h1>";

foreach ($c->ads() as $a) {
	print "<li><a href=view.php?id={$a->id}>{$a->name}</a>";
}



