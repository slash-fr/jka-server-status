<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Log;

use JkaServerStatus\Log\Logger;
use JkaServerStatus\Tests\TestCase;

final class LoggerTest extends TestCase
{
    public function testEmptyLogFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        new Logger($tempFile, LOG_INFO);
        
        $logFileContent = file_get_contents($tempFile);

        unlink($tempFile);

        $this->assertSame('', $logFileContent);
    }

    public function testReadOnlyLogFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');
        chmod($tempFile, 0400); // r-- --- ---

        $logger = new Logger($tempFile, LOG_INFO);

        // Disable error_log() temporarily - it's used as backup by the Logger, when it can't write to the log file
        // When in a unit test, it would just clutter the console output.
        $isWindows = (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0);
        $nullLogFile = $isWindows ? 'NUL' : '/dev/null';
        $previousErrorLogValue = ini_set('error_log', $nullLogFile);

        // Try writing to the read-only file
        @$logger->error(__METHOD__);
        
        // Reset "error_log"
        ini_set('error_log', $previousErrorLogValue);

        $logFileContent = file_get_contents($tempFile);

        chmod($tempFile, 0600); // rw- --- ---
        unlink($tempFile);

        $this->assertSame('', $logFileContent);
    }

    public function testErrorMessageWithErrorLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->error(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertLogFileContentHasMessage($logFileContent, Logger::ERROR, __METHOD__);
        $this->assertLogFileContentHasNbMessages($logFileContent, 1);
    }

    public function testErrorMessageWithWarningLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_WARNING);
        $logger->error(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertLogFileContentHasMessage($logFileContent, Logger::ERROR, __METHOD__);
        $this->assertLogFileContentHasNbMessages($logFileContent, 1);
    }

    public function testErrorMessageWithInfoLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $logger->error(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        
        $this->assertLogFileContentHasMessage($logFileContent, Logger::ERROR, __METHOD__);
        $this->assertLogFileContentHasNbMessages($logFileContent, 1);
    }

    public function testWarningMessageWithErrorLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->warning(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertSame('', $logFileContent);
    }

    public function testWarningMessageWithWarningLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_WARNING);
        $logger->warning(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertLogFileContentHasMessage($logFileContent, Logger::WARNING, __METHOD__);
        $this->assertLogFileContentHasNbMessages($logFileContent, 1);
    }

    public function testWarningMessageWithInfoLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $logger->warning(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertLogFileContentHasMessage($logFileContent, Logger::WARNING, __METHOD__);
        $this->assertLogFileContentHasNbMessages($logFileContent, 1);
    }

    public function testInfoMessageWithErrorLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->info(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertSame('', $logFileContent);
    }

    public function testInfoMessageWithWarningLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->info(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertSame('', $logFileContent);
    }

    public function testInfoMessageWithInfoLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $logger->info(__METHOD__);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertLogFileContentHasMessage($logFileContent, Logger::INFO, __METHOD__);
        $this->assertLogFileContentHasNbMessages($logFileContent, 1);
    }

    public function testMessageWithMultipleLines(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $line1 = __METHOD__ . 'line 1';
        $line2 = __METHOD__ . 'line 2';
        $line3 = __METHOD__ . 'line 3';
        $logger->info($line1);
        $logger->warning($line2);
        $logger->error($line3);
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertLogFileContentHasMessage($logFileContent, Logger::INFO, $line1, 0);
        $this->assertLogFileContentHasMessage($logFileContent, Logger::WARNING, $line2, 1);
        $this->assertLogFileContentHasMessage($logFileContent, Logger::ERROR, $line3, 2);
        $this->assertLogFileContentHasNbMessages($logFileContent, 3);
    }

    /**
     * Verifies that the content of a log file matches what is expected for a log message with the specified level name
     * and message.
     * @param string $logFileContent Full log file content, including newlines
     * @param string $levelName e.g. "ERROR", "WARNING", or "INFO"
     * @param string $message The message to look for, without date, log level, nor trailing newline
     * @param int $lineIndex Line number where the message should be (starts at 0)
     */
    private function assertLogFileContentHasMessage(
        string $logFileContent,
        string $levelName,
        string $message,
        int $lineIndex = 0
    ): void 
    {
        $lines = explode("\n", $logFileContent);

        $this->assertTrue(
            (bool)preg_match(
                '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} - '
                    . preg_quote($levelName) . ' - '
                    . preg_quote($message) . '$/',
                $lines[$lineIndex] ?? ''
            ),
            "The log file does not have the expected content.\n"
                . 'Expected: ' . date('Y-m-d H:i:s') . " - $levelName - $message" . "\n"
                . '     Got: ' . ($lines[$lineIndex] ?? '') . "\n"
                . '(The exact date is irrelevant)'
        );
    }

    /**
     * Verifies that the content of a log file matches what is expected for a log file with the specified number of
     * ACTUAL messages (not including the empty last line).
     * @param string $logFileContent Full log file content, including newlines
     * @param int $nbExpectedMessages Number of actually expected messages. (Does not include the empty last line).
     */
    private function assertLogFileContentHasNbMessages(string $logFileContent, int $nbExpectedMessages): void
    {
        $lines = explode("\n", $logFileContent);
        $this->assertCount(
            $nbExpectedMessages + 1,
            $lines,
            'The log file was expected to have ' . $nbExpectedMessages + 1 . 'line(s), including the empty last line.'
                . "\n"
                . "Got:\n"
                . "--------------------------------------------------------------------------------\n"
                . $logFileContent . "\n"
                . "--------------------------------------------------------------------------------"
        );

        $this->assertSame('', $lines[$nbExpectedMessages], 'The log file was expected to have an empty last line.');
    }
}
