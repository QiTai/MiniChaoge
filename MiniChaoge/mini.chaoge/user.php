<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 20:01
 */

require_once("Kijiji.php");

$id = $_GET['id'];
$o = new User();
$o->id = $id ? $id : 2001;
$o->load();

print "<h1>{$o->name}</h1><p>";

foreach ($o->ads() as $a){
	print "<li><a href=view.php?id={$a->id}>{$a->name}</a>";
}