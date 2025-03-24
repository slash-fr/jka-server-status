<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

/**
 * Logging configuration
 */
class LogConfigData
{
    /**
     * @var string $logFile Path to the log file (e.g. "/var/www/jka-server-status/var/log/server.log")
     */
    public readonly string $logFile;

    /**
     * @var int $logLevel e.g. LOG_INFO, LOG_WARNING, LOG_ERR
     */
    public readonly int $logLevel;

    /**
     * @param string $logFile  Path to the log file (e.g. "/var/www/jka-server-status/var/log/server.log")
     * @param int    $logLevel e.g. LOG_INFO, LOG_WARNING, LOG_ERR
     */
    public function __construct(string $logFile, int $logLevel) {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
    }
}
