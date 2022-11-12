#!/usr/bin/env php
<?php

use EstaleiroWeb\ED\IO\Thread;
require __DIR__.'/../vendor/autoload.php';

$arr = $done = [];
$arr[] = new Thread('benchmarkIf');
$arr[] = new Thread('benchmarkTernary');

foreach($arr as $o) {
	$o->benchmarkTime(60)->start();
}
while($arr) {
	$o=reset($arr);
	if(!$o->isAlive()) {
		$done[]=array_shift($arr);
	} else sleep(.5);
}
foreach($done as $o) print_r($o());
// print_r($done);

function benchmarkIf() {
	if (2 == 3) return 1;
	else return 0;
}
function benchmarkTernary() {
	return 2 == 3 ? 1 : 0;
}
