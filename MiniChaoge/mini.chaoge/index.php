<?php

header("Content-type : text/html ; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once("Kijiji.php");	//----forgot

$id = $_GET['id'];
$c = new Category();
$c->id = $id ? $id : 21;
$c->load();


foreach ($c->children() as $cc) {

	print "<b><a href=listing.php?id={$cc->id}>{$cc->name}</a></b><p>";

	foreach ($cc->children() as $cs) {
		print "<li><a href=listing.php?id={$cs->id}>{$cs->name}</a><br>";
	}
}