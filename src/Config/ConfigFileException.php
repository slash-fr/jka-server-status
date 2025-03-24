<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

/**
 * Use this class when config.php does not exists, or cannot be read.
 */
class ConfigFileException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}