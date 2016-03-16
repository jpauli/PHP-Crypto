<?php
namespace JPauli\Crypto;

class RuntimeException extends \RuntimeException
{
    public function __construct(string $message, ...$args)
    {
        parent::__construct(vsprintf($message."\n", $args));
    }
}