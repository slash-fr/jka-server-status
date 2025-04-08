<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

class LogConfigService
{
    /**
     * Path to the default log file.
     */
    private const DEFAULT_LOG_FILE = __DIR__ . '/../../var/log/server.log';

    /**
     * Initializes the log configuration from the specified configuration file.
     * @param string $pathToconfigFile Path to the PHP config file (e.g. __DIR__ . '/../config.php')
     *                                 It MUST exist and be readable.
     * @throws LogConfigException if the log configuration is invalid.
     */
    public function getLogConfig(string $pathToConfigFile): LogConfigData
    {
        require $pathToConfigFile;

        $logLevel = $this->sanitizeLogLevel($log_level ?? null);
        if ($logLevel < 1) { // Logging is disabled
            // Return immediately (don't check the $log_file variable)
            return new LogConfigData(self::DEFAULT_LOG_FILE, $logLevel);
        }

        $logFile = $this->sanitizeLogFile($log_file ?? null);
        
        //$this->verifyLogFileWritability($logFile, $pathToConfigFile);
        // Actually, don't crash the page if the log file is (or BECOMES) unwritable...

        return new LogConfigData($logFile, $logLevel);
    }

    /**
     * Sanitizes the log level.
     * @param mixed $logLevel The $log_level value found in config.php, or null if not set.
     * @throws LogConfigException if the $log_level variable is invalid.
     */
    private function sanitizeLogLevel(mixed $logLevel): int
    {
        if (!isset($logLevel)) {
            return LOG_INFO; // Default value
        }

        if (!is_int($logLevel)) {
            throw new LogConfigException(
                'Config variable $log_level must be an int (got: ' . gettype($logLevel) . '). '
                . 'Tip: use one of the constants (LOG_INFO, LOG_WARNING, LOG_ERR), or 0 to disable logging.'
            );
        }

        return $logLevel;
    }

    /**
     * Sanitizes the log file variable.
     * @param mixed $logFile The $log_file value found in config.php, or null if not set.
     * @return string $Path to the log file
     * @throws LogConfigException if the $log_file variable is invalid.
     */
    private function sanitizeLogFile(mixed $logFile): string
    {
        if (!isset($logFile)) {
            return self::DEFAULT_LOG_FILE;
        }
        
        if (!is_string($logFile)) {
            throw new LogConfigException(
                'Config variable $log_file must be a string (got: ' . gettype($logFile) . ').'
            );
        }

        return $logFile;
    }

    /**
     * Verifies that the specified path is writable.
     * @param string $logFile Path to the desired application-level log file.
     * @throws LogConfigException if the log file is not writable
     */
    private function verifyLogFileWritability(string $logFile): void
    {
        //if (file_exists($logFile) && filemtime($logFile) >= filemtime($pathToConfigFile)) {
        //    // Do NOT check writability if the log file is NEWER than "config.php".
        //    // Detecting an initial configuration error is nice, but...
        //    // ...we wouldn't want to crash every page if the log file BECOMES unwritable.
        //    return;
        //}

        $handle = fopen($logFile, 'a'); // Append mode
        // Using fopen() rather than is_writable(), because the log file might not exist yet.
        if ($handle === false) {
            fclose($handle);
            throw new LogConfigException(
                'Could not open the application-level log file for writing ("' . $logFile . '").'
            );
        }
        fclose($handle);
    }
}
