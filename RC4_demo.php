#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use JPauli\Crypto\RC4;

if (ini_get('register_argc_argv') == 0) {
	echo "You must activate 'register_argc_argv' in php.ini \n";
	exit(1);
}

if ($argc < 2) {
	printf("Usage : %s key [iv=time()] \n", $argv[0]);
	exit(1);
}

$key = $argv[1];
$iv  = $argv[2] ?? time();



$rc4 = new RC4($iv, $key);

while(1) {
	$byte = $rc4->output();
	printf("PRB : %02X\n", ord($byte));
	usleep(50000);
}