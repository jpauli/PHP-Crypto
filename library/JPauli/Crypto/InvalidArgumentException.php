<?php
namespace JPauli\Crypto;

class InvalidArgumentException extends \InvalidArgumentException
{
    public function __construct(string $message, ...$args)
    {
        parent::__construct(vsprintf($message."\n", $args));
    }
}