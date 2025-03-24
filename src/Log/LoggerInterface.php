<?php declare(strict_types=1);

namespace JkaServerStatus\Log;

interface LoggerInterface
{
    /**
     * Logs an application-level INFO message
     * @param string $message The info message to log
     */
    public function info(string $message): void;

    /**
     * Logs an application-level WARNING message
     * @param string $message The warning message to log
     */
    public function warning(string $message): void;

    /**
     * Logs an application-level ERROR message
     * @param string $message The error message to log
     */
    public function error(string $message): void;
}
