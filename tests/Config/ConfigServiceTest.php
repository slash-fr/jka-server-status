<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Config;

use JkaServerStatus\Config\ConfigException;
use JkaServerStatus\Config\ConfigService;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Tests\Log\MockLogger;
use JkaServerStatus\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ConfigServiceTest extends TestCase
{
    public function testValidConfig1(): void
    {
        $logger = new MockLogger();
        $configService = new ConfigService($logger);
        $config = $configService->getConfig(__DIR__ . '/../sample_configs/valid_config_1.php');

        // "test_config_1.php" is mostly empty, apart from one server, with only the required "address" field,
        // so we're basically just testing default values
        $this->assertSame(10, $config->cachingDelay);
        $this->assertSame(3, $config->timeoutDelay);
        $this->assertSame('', $config->assetUrl);
        $this->assertSame(false, $config->isLandingPageEnabled);
        $this->assertSame('/', $config->landingPageUri);
        $this->assertSame(false, $config->isAboutPageEnabled);
        $this->assertSame('/about', $config->aboutPageUri);
        $this->assertSame('About', $config->aboutPageTitle);
        $this->assertSame(1, count($config->jkaServers));
        $this->assertSame('/', $config->jkaServers[0]->uri ?? null);
        $this->assertSame('192.0.2.1', $config->jkaServers[0]->address ?? null);
        $this->assertSame('192.0.2.1', $config->jkaServers[0]->name ?? null);
        $this->assertSame('Windows-1252', $config->jkaServers[0]->charset ?? null);

        // A valid config file shouldn't generate errors nor warnings
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));
    }

    public function testValidConfig2(): void
    {
        $logger = new MockLogger();
        $configService = new ConfigService($logger);
        $config = $configService->getConfig(__DIR__ . '/../sample_configs/valid_config_2.php');

        $this->assertSame(9, $config->cachingDelay);
        $this->assertSame(2, $config->timeoutDelay);
        $this->assertSame('/prefix', $config->assetUrl);
        $this->assertSame(false, $config->isLandingPageEnabled);
        $this->assertSame('/server-list', $config->landingPageUri);
        $this->assertSame(true, $config->isAboutPageEnabled);
        $this->assertSame('/tell-me-about-it', $config->aboutPageUri);
        $this->assertSame('Credits (and legal stuff)', $config->aboutPageTitle);
        $this->assertSame(2, count($config->jkaServers));
        $this->assertSame('/main-server', $config->jkaServers[0]->uri ?? null);
        $this->assertSame('192.0.2.1', $config->jkaServers[0]->address ?? null);
        $this->assertSame('^5M^7ain ^5S^7erver', $config->jkaServers[0]->name ?? null);
        $this->assertSame('', $config->jkaServers[0]->subtitle ?? null);
        $this->assertSame('Windows-1252', $config->jkaServers[0]->charset ?? null);
        $this->assertSame('/secondary-server', $config->jkaServers[1]->uri ?? null);
        $this->assertSame('jka.example.com:29071', $config->jkaServers[1]->address ?? null);
        $this->assertSame('^3Secondary ^7Server', $config->jkaServers[1]->name ?? null);
        $this->assertSame('Server location: Earth', $config->jkaServers[1]->subtitle ?? null);
        $this->assertSame('UTF-8', $config->jkaServers[1]->charset ?? null);

        // A valid config file shouldn't generate errors nor warnings
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));
    }

    public static function invalidConfigDataProvider()
    {
        // Arguments: [File number, What the error message should start with]
        yield [1, 'Config variable $caching_delay must be an int'];
        yield [2, 'Config variable $timeout_delay must be an int'];
        yield [3, 'Config variable $timeout_delay must be >= 1'];
        yield [4, 'Config variable $asset_url must be a string'];
        yield [5, 'Config variable $jka_servers is required.'];
        yield [6, 'Config variable $jka_servers must be an array'];
        yield [7, 'Config variable $jka_servers must contain at least 1 server'];
        yield [8, 'Config variable $jka_servers must be an array of arrays'];
        yield [9, 'A "uri" field is required for each server (when multiple servers are configured)'];
        yield [10, 'The "uri" of each configured server must be a string'];
        yield [11, 'Each configured server must specify an "address". $jka_servers[0] does not specify an "address".'];
        yield [12, 'The "address" of each configured server must be a string'];
        yield [13, 'Invalid JKA server address'];
        yield [14, 'The "name" of each configured server must be a string'];
        yield [15, 'The "charset" of each configured server must be a string'];
        yield [16, 'Unsupported "charset"'];
        yield [17, 'Config variable $enable_landing_page must be a boolean'];
        yield [18, 'Config variable $landing_page_uri must be a string'];
        yield [19, '$jka_servers[1]["uri"] conflicts with $jka_servers[0]["uri"]'];
        yield [20, '$jka_servers[0]["uri"] conflicts with the landing page URI'];
        yield [21, 'The "subtitle" of each configured server must be a string'];
        yield [22, 'Config variable $enable_about_page must be a boolean'];
        yield [23, 'Config variable $about_page_uri must be a string'];
        yield [24, 'Config variable $about_page_title must be a string'];
        yield [25, 'Config variable $about_page_uri conflicts with the landing page URI'];
        yield [26, '$jka_servers[0]["uri"] conflicts with the "About" page URI'];
    }

    #[DataProvider('invalidConfigDataProvider')]
    public function testInvalidConfig(int $i, string $errorMessageStart): void
    {
        $logger = new MockLogger();
        $configService = new ConfigService($logger);

        $gotException = false;

        try {
            $configService->getConfig(__DIR__ . "/../sample_configs/invalid_config_$i.php");
        } catch (ConfigException $exception) {
            $gotException = true;
            $this->assertStringStartsWith($errorMessageStart, $exception->getMessage());
        }

        $this->assertTrue(
            $gotException,
            "Did not catch the expected ConfigException for invalid_config_$i.php"
        );

        $errorMessages = $logger->getMessages([Logger::ERROR]);
        $this->assertCount(1, $errorMessages, "Expected 1 error message for invalid_config_$i.php");
        $this->assertStringStartsWith($errorMessageStart, $errorMessages[0]);
    }
}
