#!/usr/bin/php
<?php
require 'vendor/autoload.php';
# /home/helbertfernandes/ed/ssh.php

use phpseclib3\Net\SSH2;

$line = [
	'User' => 'coreprv',
	'Passwd' => 'preventive#1',
	'Ip' => '10.221.113.224',
	'Port' => '22',
];

$ssh = new SSH2($line['Ip'], $line['Port']);
//if ($expected != $ssh->getServerPublicHostKey()) {
//    throw new \Exception('Host key verification failed');
//}

$ssh->login($line['User'], $line['Passwd']);
echo $ssh->exec('ls -latr');
