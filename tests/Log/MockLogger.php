<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Log;

use JkaServerStatus\Log\Logger;
use JkaServerStatus\Log\LoggerInterface;

/**
 * Logger that does not write anything to disk
 */
class MockLogger implements LoggerInterface
{
    /**
     * @var string[] $messages
     */
    private array $messages = [];

    ////////////////////////////////////////////////////////////////////////////
    // Public methods

    public function info(string $message): void
    {
        $this->log(Logger::INFO, $message);
    }

    public function warning(string $message): void
    {
        $this->log(Logger::WARNING, $message);
    }

    public function error(string $message): void
    {
        $this->log(Logger::ERROR, $message);
    }

    /**
     * Gets the log messages recorded since the instanciation of the MockLogger, that match the specified levels.
     * @param string[] $levels Use the constants: Logger::ERROR, Logger::WARNING, Logger::INFO
     * @return string[] The log messages matching the specified levels.
     */
    public function getMessages(array $levels = [Logger::ERROR, Logger::WARNING, Logger::INFO]): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            foreach ($levels as $level) {
                $prefix = "$level - ";
                if (str_starts_with($message, $prefix)) {
                    $messages[] = substr($message, strlen($prefix));
                }
            }
        }
        return $messages;
    }

    // End of public methods
    ////////////////////////////////////////////////////////////////////////////

    private function log(string $levelName, string $message): void
    {
        // Just store the message in memory (don't append it to a file)
        $this->messages[] = "$levelName - $message";
        // Not prefixed by the date/time, because:
        // - Unit tests usually run in less than 1 second,
        // - It would make it more difficult to "parse" the message (to verify its validity).
    }
}
