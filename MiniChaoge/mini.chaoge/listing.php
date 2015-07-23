<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 20:01
 */
require_once("Kijiji.php");									//area.php和listing.php代码只有一个地方不同，完全可以复用；

$id = $_GET['id'];
$c = new Category();
$c->id = $id ? $id : 2001;
$c->load();

print "<h1>{$c->name}</h1><p>";

foreach ($c->toRoot() as $cc) {
	print "<a href=listing.php?id={$cc->id}>{$cc->name}</a>|";
}

print "<p>";

foreach ($c->children() as $cs) {
	print "<a href=listing.php?id={$cs->id}>{$cs->name}</a>|";
}

print "<p>";

foreach ($c->ads() as $a){
	print "<li><a href=view.php?id={$a->id}>{$a->name}</a>";
}