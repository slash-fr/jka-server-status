<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Config;

use JkaServerStatus\Config\LogConfigException;
use JkaServerStatus\Config\LogConfigService;
use JkaServerStatus\Tests\TestCase;

final class LogConfigTest extends TestCase
{
    public function testInvalidLogLevel(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logConfigService->getLogConfig(__DIR__ . '/../sample_configs/invalid_log_level.php');
        } catch (LogConfigException $exception) {
            $gotException = true;
        }
        $this->assertTrue($gotException, 'Did not catch the expected LogConfigException.');
    }

    public function testInvalidLogFile(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logConfigService->getLogConfig(__DIR__ . '/../sample_configs/invalid_log_file.php');
        } catch (LogConfigException $exception) {
            $gotException = true;
        }
        $this->assertTrue($gotException, 'Did not catch the expected LogConfigException.');
    }

    public function testLogFileUnwritable(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logFile = tempnam(sys_get_temp_dir(), 'JKA');
            chmod($logFile, 0400);
            $logConfigService->getLogConfig($logFile);
        } catch (LogConfigException $exception) {
            $gotException = true;
        } finally {
            chmod($logFile, 0600);
            unlink($logFile);
        }
        $this->assertFalse($gotException, 'Unwritable log files are not supposed to generate exceptions.');
    }

    public function testValidInfoConfig(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logConfig = $logConfigService->getLogConfig(__DIR__ . '/../sample_configs/valid_log_config_info.php');
            $this->assertSame(LOG_INFO, $logConfig->logLevel, 'The log level was not the expected LOG_INFO.');
        } catch (LogConfigException $exception) {
            $gotException = true;
        }
        $this->assertFalse($gotException, 'Unexpected LogConfigException.');
    }

    public function testValidWarningConfig(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logConfig = $logConfigService->getLogConfig(__DIR__ . '/../sample_configs/valid_log_config_warning.php');
            $this->assertSame(LOG_WARNING, $logConfig->logLevel, 'The log level was not the expected LOG_WARNING.');
        } catch (LogConfigException $exception) {
            $gotException = true;
        }
        $this->assertFalse($gotException, 'Unexpected LogConfigException.');
    }

    public function testValidErrorConfig(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logConfig = $logConfigService->getLogConfig(__DIR__ . '/../sample_configs/valid_log_config_error.php');
            $this->assertSame(LOG_ERR, $logConfig->logLevel, 'The log level was not the expected LOG_ERR.');
        } catch (LogConfigException $exception) {
            $gotException = true;
        }
        $this->assertFalse($gotException, 'Unexpected LogConfigException.');
    }

    public function testValidNoLoggingConfig(): void
    {
        $logConfigService = new LogConfigService();
        $gotException = false;
        try {
            $logConfig = $logConfigService->getLogConfig(__DIR__ . '/../sample_configs/valid_log_config_no_logging.php');
            $this->assertSame(0, $logConfig->logLevel, 'The log level did not have the expected value.');
        } catch (LogConfigException $exception) {
            $gotException = true;
        }
        $this->assertFalse($gotException, 'Unexpected LogConfigException.');
    }
}
