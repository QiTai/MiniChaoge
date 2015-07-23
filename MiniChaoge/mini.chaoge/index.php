<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 20:01
 */
header("Content-type : text/html ; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once("Kijiji.php");

$id = $_GET['id'];
$o = new Category();
$o->id = $id ? $id : 21;
$o->load();

foreach ($o->children() as $cc) {
	print "<b><a href=listing.php?id={$cc->id}>{$cc->name}</a><b><p>";
	foreach ($cc->children() as $cs) {
		print "<li><a href=listing.php?id={$cs->id}>{$cs->name}</a><br>";
	}
}