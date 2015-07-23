<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 20:01
 */


header("Content-type:text/html;charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once("Kijiji.php");

$id = $_GET['id'];
$c = new User();
$c->id = $id ? $id : 2001;
$c->load();

print "<h1>{$c->name}</h1>";

foreach ($c->ads() as $a) {
	print "<li><a href=view.php?id={$a->id}>{$a->name}</a><br>";
}
