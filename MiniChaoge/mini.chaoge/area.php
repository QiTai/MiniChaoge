<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 20:00
 */

header("Content-type : text/html ; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);
require_once("Kijiji.php");

$id = $_GET['id'];
$o = new Area();
$o->id = $id ? $id : 2001;
$o->load();

print "<h1>{$o->name}</h1><p>";

foreach ($o->toRoot() as $cc) {
	print "<a href=listing.php?id={$cc->id}>{$cc->name}</a>|";
}

print "<p>";

foreach ($o->children() as $cs) {
	print "<a href=listing.php?id={$cs->id}>{$cs->name}</a>|";
}

print "<p>";

foreach ($o->ads() as $a){
	print "<li><a href=view.php?id={$a->id}>{$a->name}</a>";
}