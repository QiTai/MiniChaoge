<?php

header("Content-type: text/html; charset = utf-8");
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
require_once("Kijiji.php");

$id = $_GET['id'];
$c = new Area();
$c->id = $id ? $id : 12432;      //-----------------------forgot number
$c->load();

print "<h1>$c->name</h1>";

foreach ($c->toRoot() as $cc) {
	echo "<a href=area.php?id={$cc->id}>{$cc->name}</a>|";
}

print "<p>";

foreach ($c->children() as $cc) {
	echo "<a href=area.php?id={$cc->id}>{$cc->name}</a>|";
}

foreach ($c->ads() as $a) {
	echo "<li><a href=view.php?id={$a->id}>{$a->name}</a>";
}