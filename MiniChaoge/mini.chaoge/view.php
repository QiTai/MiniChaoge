<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 19:59
 */


header("Content-type : text/html ; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once ("Kijiji.php");

$id = $_GET['id'];
$c = new Ad();
$c->id = $id ? $id : 2001;
$c->load();

print "<h1>{$c->name}</h1><p><b>publisher:<a href=user.php?id={$c->user->id}>{$c->user->load()->name}</a></b><p>";

if ($c->category->load()) {
	foreach ($c->category->load()->toRoot() as $cc) {
		print "<a href=listing.php?id={$cc->id}>{$cc->name}</a>|";
	}
}

print "<p>";

if ($c->area->load()) {
	foreach ($c->area->load()->toRoot() as $cc) {
		print "<a href=area.php?id={$cc->id}>{$cc->name}</a>|";
	}
}

print "<p>";

print "$c->content<p>";

foreach ($c->comments() as $cc) {
	print "<li><a href=user.php?id={$cc->userId}>{$cc->userNick}</a>:{$cc->content}<br>";
}
