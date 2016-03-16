#!/usr/bin/env php
<?php
/*
 * Usage example :    > ./lfsr_demo.php "my secret key" 16
 */
require_once __DIR__ . '/vendor/autoload.php';

use JPauli\Crypto\LFSR\LFSR;

if (ini_get('register_argc_argv') == 0) {
	echo "You must activate 'register_argc_argv' in php.ini \n";
	exit(1);
}

if ($argc < 3 || $argc > 4) {
	printf("Usage : %s start-state degree{3-%d} [speed]{1-100} \n", $argv[0], LFSR::LFSR_MAX_DEGREE);
	exit(1);
}

$lfsr = new LFSR($argv[2], $argv[1], $argv[3] ?? null);

foreach ($lfsr->demoRun() as $iter => $value)
{
	$lfsr->printCurrentState();
	$lfsr->pause();
}

printf("LFSR just finished looping all states - %d iterations done", $iter);
