<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Config;

use JkaServerStatus\Config\ConfigFileException;
use JkaServerStatus\Config\ConfigFileService;
use JkaServerStatus\Tests\TestCase;

class ConfigFileServiceTest extends TestCase
{
    public function testNonExistingFile(): void
    {
        $configFileService = new ConfigFileService();
        $gotException = false;
        try {
            $configFileService->getConfigFile(__DIR__ . "/../sample_configs/DO-NOT-CREATE-THIS-FILE.php");
        }
        catch(ConfigFileException $exception) {
            $gotException = true;
            $this->assertStringStartsWith('Could not find the configuration file', $exception->getMessage());
        }
        $this->assertTrue($gotException, 'Did not catch the expected ConfigFileException.');
    }

    public function testIsFile(): void
    {
        $configFileService = new ConfigFileService();
        $gotException = false;
        try {
            $configFileService->getConfigFile(__DIR__ . "/../sample_configs/");
        }
        catch(ConfigFileException $exception) {
            $gotException = true;
            $this->assertStringStartsWith('The configuration file is not a regular file', $exception->getMessage());
        }
        $this->assertTrue($gotException, 'Did not catch the expected ConfigFileException.');
    }

    public function testUnreadableFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'JKA');
        chmod($tempFile, 0000); // --- --- ---
        // Problem: chmod 000 does not work on Windows
        $isWindows = (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0);
        if ($isWindows) {
            echo "\nWARNING: chmod 000 does not work on Windows. Skipping " . __METHOD__ . "().\n";
            return;
        }

        $configFileService = new ConfigFileService();
        $gotException = false;
        try {
            $configFileService->getConfigFile($tempFile);
        }
        catch(ConfigFileException $exception) {
            $gotException = true;
            $this->assertStringStartsWith('Could not read the configuration file', $exception->getMessage());
        } finally {
            chmod($tempFile, 0600); // rw- --- ---
            unlink($tempFile);
        }
        $this->assertTrue($gotException, 'Did not catch the expected ConfigFileException.');
    }

    public function testValidFile(): void
    {
        $configFileService = new ConfigFileService();
        $gotException = false;
        try {
            $configFileService->getConfigFile(__DIR__ . "/../sample_configs/valid_config_1.php");
        }
        catch(ConfigFileException $exception) {
            $gotException = true;
        }
        $this->assertFalse($gotException, 'Unexpected ConfigFileException.');
    }
}
