<?php

declare(strict_types=1);

/**
 * Basic Logger. Can be extended
 */
class Logger
{
    protected string $logFile;
    protected int $level;

    public function __construct(string $logFile, int $level = LOG_INFO)
    {
        $this->logFile = $logFile;
        $this->level = $level;
    }

    /**
     * Logs an application-level INFO message
     * @param string $message The info message to log
     */
    public function info(string $message): void
    {
        if ($this->level >= LOG_INFO) {
            $this->log('INFO', $message);
        }
    }

    /**
     * Logs an application-level WARNING message
     * @param string $message The warning message to log
     */
    public function warning(string $message): void
    {
        if ($this->level >= LOG_WARNING) {
            $this->log('WARNING', $message);
        }
    }

    /**
     * Logs an application-level ERROR message
     * @param string $message The error message to log
     */
    public function error(string $message): void
    {
        if ($this->level >= LOG_ERR) {
            $this->log('ERROR', $message);
        }
    }

    /**
     * Logs an application-level message
     * @param string $levelName e.g. "INFO", "WARNING", "ERROR"
     * @param string $message The message to log
     */
    protected function log(string $levelName, string $message): void
    {
        if ($this->level < 1) { // If logging is disabled
            return; // Don't log
        }

        $formattedMessage = date('Y-m-d H:i:s') . " - $levelName - $message\n";

        $bytesWritten = @file_put_contents(
            $this->logFile,
            $formattedMessage,
            FILE_APPEND,
        );

        if ($bytesWritten === false) { // Fallback to the
            error_log(
                date('Y-m-d H:i:s') . ' - ERROR - Could not write to the application-level log file '
                . '("' . $this->logFile . '").'
            );
            error_log($formattedMessage);
        }
    }
}
