<?php declare(strict_types=1);

namespace JkaServerStatus\Log;

/**
 * Config Logger. Allows you to update the log file and level after instanciation. Use it sparingly.
 */
class ConfigLogger extends Logger
{
    public function __construct(string $logFile, int $level = LOG_INFO)
    {
        parent::__construct($logFile, $level);
    }

    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}
