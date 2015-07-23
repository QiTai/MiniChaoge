<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 19:59
 */

require_once("Kijiji.php");

$id = $_GET['id'];
$o = new Ad();
$o->id = $id ? $id : 2001;
$o->load();

print "<h1>{$o->name}</h1><p>publisher:<a href=user.php?id={$o->user->id}>{$o->user->load()->name}</a><p>";

if ($o->category->load()) {
	foreach ($o->category->load()->toRoot() as $cc) {
		print "<a href=listing.php?id={$cc->id}>{$cc->name}</a>|";
	}
}

print "<p>";

if ($o->area->load()) {
	foreach ($o->area->load()->toRoot() as $cc) {
		print "<a href=area.php?id={$cc->id}>{$cc->name}</a>|";
	}
}

print "<p>";

print "{$o->content}<p>";

foreach ($o->comments() as $c) {
	print "<li><a href=listing.php?id={$c->id}>{$c->name}</a>:{$c->content}<br>";
}





