<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public function testEmptyLogFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        
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
        $isWindows = (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0);
        $nullLogFile = $isWindows ? 'NUL' : '/dev/null';
        $previousErrorLogValue = ini_set('error_log', $nullLogFile);

        // Try writing to the read-only file
        $logger->error('Test');
        
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
        $logger->error('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertStringContainsString('ERROR - Test', $logFileContent);
    }

    public function testErrorMessageWithWarningLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_WARNING);
        $logger->error('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertStringContainsString('ERROR - Test', $logFileContent);
    }

    public function testErrorMessageWithInfoLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $logger->error('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertStringContainsString('ERROR - Test', $logFileContent);
    }

    public function testWarningMessageWithErrorLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->warning('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertSame('', $logFileContent);
    }

    public function testWarningMessageWithWarningLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_WARNING);
        $logger->warning('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertStringContainsString('WARNING - Test', $logFileContent);
    }

    public function testWarningMessageWithInfoLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $logger->warning('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertStringContainsString('WARNING - Test', $logFileContent);
    }

    public function testInfoMessageWithErrorLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->info('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertSame('', $logFileContent);
    }

    public function testInfoMessageWithWarningLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_ERR);
        $logger->info('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertSame('', $logFileContent);
    }

    public function testInfoMessageWithInfoLevel(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');

        $logger = new Logger($tempFile, LOG_INFO);
        $logger->info('Test');
        $logFileContent = file_get_contents($tempFile);
        unlink($tempFile);
        $this->assertStringContainsString('INFO - Test', $logFileContent);
    }
}
