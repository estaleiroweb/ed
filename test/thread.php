#!/usr/bin/env php
<?php

use EstaleiroWeb\ED\IO\Thread;
use EstaleiroWeb\ED\IO\Threads;
require __DIR__.'/../vendor/autoload.php';

$t = new Threads('benchmarkIf','benchmarkTernary');

$t->benchmarkTime(2)->start();
$t->wait();
print_r($t());
// print_r($done);

function benchmarkIf() {
	if (2 == 3) return 1;
	else return 0;
}
function benchmarkTernary() {
	return 2 == 3 ? 1 : 0;
}
