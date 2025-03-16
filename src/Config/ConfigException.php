<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

class ConfigException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
