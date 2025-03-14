<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public function testValidConfig1(): void
    {
        $configLogger = new ConfigLogger('php://stderr', LOG_WARNING);
        $config = new Config(__DIR__ . '/sample-configs/valid-config-1.php', $configLogger, 'php://stderr');
        $this->assertSame('php://stdout', $config->logFile);
        $this->assertSame(LOG_ERR, $config->logLevel);
        $this->assertSame(9, $config->cachingDelay);
        $this->assertSame(2, $config->timeoutDelay);
        $this->assertSame('/prefix', $config->rootUrl);
        $this->assertSame(false, $config->isLandingPageEnabled);
        $this->assertSame('/server-list', $config->landingPageUri);
        $this->assertSame(2, count($config->jkaServers));
        $this->assertSame('/main-server', $config->jkaServers[0]->uri ?? null);
        $this->assertSame('192.0.2.1', $config->jkaServers[0]->address ?? null);
        $this->assertSame('^5M^7ain ^5S^7erver', $config->jkaServers[0]->name ?? null);
        $this->assertSame('Windows-1252', $config->jkaServers[0]->charset ?? null);
        $this->assertSame('/secondary-server', $config->jkaServers[1]->uri ?? null);
        $this->assertSame('jka.example.com:29071', $config->jkaServers[1]->address ?? null);
        $this->assertSame('^3Secondary ^7Server', $config->jkaServers[1]->name ?? null);
        $this->assertSame('UTF-8', $config->jkaServers[1]->charset ?? null);
    }

    public function testValidConfig2(): void
    {
        $configLogger = new ConfigLogger('php://stdout', LOG_WARNING);
        $defaultLogFile = 'php://stderr';
        $config = new Config(__DIR__ . '/sample-configs/valid-config-2.php', $configLogger, $defaultLogFile);
        // "test-config-2.php" is mostly empty, apart from one server, with only the required "address" field,
        // so we're basically just testing default values
        $this->assertSame($defaultLogFile, $config->logFile);
        $this->assertSame(LOG_INFO, $config->logLevel);
        $this->assertSame(10, $config->cachingDelay);
        $this->assertSame(3, $config->timeoutDelay);
        $this->assertSame('', $config->rootUrl);
        $this->assertSame(false, $config->isLandingPageEnabled);
        $this->assertSame('/', $config->landingPageUri);
        $this->assertSame(1, count($config->jkaServers));
        $this->assertSame('/', $config->jkaServers[0]->uri ?? null);
        $this->assertSame('127.0.0.1', $config->jkaServers[0]->address ?? null);
        $this->assertSame('127.0.0.1', $config->jkaServers[0]->name ?? null);
        $this->assertSame('Windows-1252', $config->jkaServers[0]->charset ?? null);
    }

    public function testInvalidConfigs(): void
    {
        $isWindows = (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0);
        $logFile = $isWindows ? 'NUL' : '/dev/null';
        $configLogger = new ConfigLogger($logFile, 0); // No logging

        $expectedMessages = [
            1 => 'Config variable $jka_servers must be an array of arrays (got: string for $jka_servers[\'address\'])',
            2 => 'Config variable $jka_servers is required.',
            3 => 'Each configured server must specify an "address". $jka_servers[0] does not specify an "address".',
        ];

        // Iterate over all the "invalid-config-*.php" files
        for ($i = 1; $i <= 3; $i++) {
            $gotException = false;
            try {
                $config = new Config(__DIR__ . "/sample-configs/invalid-config-$i.php", $configLogger, $logFile);
            } catch (ConfigException $exception) {
                $gotException = true;
                $this->assertSame($expectedMessages[$i], $exception->getMessage());
            }
            $this->assertTrue(
                $gotException,
                "Did not catch the expected ConfigException for invalid-config-$i.php"
            );
        }
    }
}
