#!/usr/bin/env php

<?php
require_once __DIR__ . '/vendor/autoload.php';

use JPauli\Crypto\LFSR\StreamCipher;

if (ini_get('register_argc_argv') == 0) {
	echo "You must activate 'register_argc_argv' in php.ini \n";
	exit(1);
}

if ($argc != 2) {
	printf("Usage : %s seed \n", $argv[0]);
	exit(1);
}

$content = trim(stream_get_contents(STDIN));
$cipher  = new StreamCipher($argv[1], strlen($content), true);

$crypted   = $cipher->cipher($content);


printf("Your clear data '%s' has been crypted as '%s'\n", $content, $crypted);

$decrypted = $cipher->cipher($crypted);

assert($content === $decrypted);