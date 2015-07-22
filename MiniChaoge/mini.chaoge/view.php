<?php

header("Content-type: text/html; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once("Kijiji.php");

$id = $_GET['id'];
$c = new Ad();
$c->id = $id ? $id : 11652477;         //-----forgot default number
$c->load();

print "<h1>$c->name</h1><p>publisher:<a href=user.php?id={$c->user->id}>{$c->user->load()->name}</a></p>"; //----totally wrong
if ($c->category->load()) {										//-----在View.php的toRoot()之前加了判断
	foreach ($c->category->load()->toRoot() as $cc) {			//-----forgot load()
		print "<a href=listing.php?id={$cc->id}>{$cc->name}</a>|";
	}
}

print "<p>";
if ($c->area->load()) {											//-----在View.php的toRoot()之前加了判断
	foreach ($c->area->load()->toRoot() as $cs) {				//---forgot load()
		print "<a href=area.php?id={$cs->id}>{$cs->name}</a>|";
	}
}

print "<p>$c->content<p>";

foreach ($c->comments() as $cc) {
	print "<li><a href=user.php?id={$cc->userId}>{$cc->userNick}</a>:$cc->content<br>";
}
