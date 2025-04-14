<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\JkaServer;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Config\JkaServerConfigData;
use JkaServerStatus\Helper\TemplateHelper;
use JkaServerStatus\JkaServer\JkaServerServiceInterface;
use JkaServerStatus\JkaServer\PlayerData;
use JkaServerStatus\JkaServer\StatusController;
use JkaServerStatus\JkaServer\StatusData;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Tests\Log\MockLogger;
use JkaServerStatus\Tests\TestCase;

final class StatusControllerTest extends TestCase
{
    private string $cacheDir;
    private MockLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary cache directory
        $this->cacheDir = tempnam(sys_get_temp_dir(), 'JKA');
        $this->assertNotSame(false, $this->cacheDir);
        // Convert the file created by tempnam() into a directory
        $this->assertTrue(unlink($this->cacheDir));
        $this->assertTrue(mkdir($this->cacheDir));

        $this->logger = new MockLogger();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        rmdir($this->cacheDir);
    }

    public function testErrorWhenCachedFileIsDirectory(): void
    {
        $jkaServerAddress = '127.0.0.1';

        // Create a DIRECTORY with the same name as the expected cache file
        $cacheFile = $this->cacheDir . '/127-0-0-1.html';
        $this->assertTrue(mkdir($cacheFile));

        // Rendering should work...
        $this->performRenderWithUpStatus($jkaServerAddress, 10);

        // ...but caching shouldn't work
        $errorMessages = $this->logger->getMessages([Logger::ERROR]);
        $this->assertCount(2, $errorMessages);
        $this->assertStringContainsString('The cached version is not a regular file', $errorMessages[0]);
        $this->assertStringContainsString('Could not cache the HTML', $errorMessages[1]);

        // Not expecting any warning messages, though
        $warningMessages = $this->logger->getMessages([Logger::WARNING]);
        $this->assertCount(0, $warningMessages, '$warningMessages = ' . print_r($warningMessages, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        // Clean up
        rmdir($cacheFile);
        unlink($cacheFile . '.lock');
    }

    public function testErrorWhenLockFileIsReadOnly(): void
    {
        $jkaServerAddress = 'example.com';
        $cacheFile = $this->cacheDir . '/example-com.html'; 

        // Create a file with the same name as the expected lock file
        $lockFile = $cacheFile . '.lock';
        $this->assertTrue(touch($lockFile));
        $this->assertTrue(chmod($lockFile, 0444)); // Make it read-only

        // Rendering should work...
        $this->performRenderWithUpStatus($jkaServerAddress, 10);

        // ...but caching shouldn't work
        $errorMessages = $this->logger->getMessages([Logger::ERROR]);
        $this->assertCount(1, $errorMessages, '$errorMessages = ' . print_r($errorMessages, true));
        $this->assertStringContainsString('Could not open the cache lock file', $errorMessages[0]);

        // Not expecting any warning messages, though
        $warningMessages = $this->logger->getMessages([Logger::WARNING]);
        $this->assertCount(0, $warningMessages, '$warningMessages = ' . print_r($warningMessages, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        // Clean up
        unlink($cacheFile);
        chmod($lockFile, 0666); // Read + write
        unlink($lockFile);
    }

    public function testErrorWhenCachedFileIsReadonly(): void
    {
        $jkaServerAddress = 'example.com';
        $cacheFile = $this->cacheDir . '/example-com.html';

        $this->assertFalse(file_exists($cacheFile));

        // Render for the first time => Generates the cache file
        $this->performRenderWithTimeoutStatus($jkaServerAddress, 10);

        $this->assertTrue(file_exists($cacheFile));

        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        // Set the modification time to 11 seconds ago
        $this->assertTrue(touch($cacheFile, time() - 11));
        // Make it readonly
        $this->assertTrue(chmod($cacheFile, 0444)); // r-- r-- r--

        ///////////////////////////
        // Rendering should work...
        $this->performRenderWithUpStatus($jkaServerAddress, 10); // 10 second caching delay

        // ...but caching shouldn't work
        $errorMessages = $this->logger->getMessages([Logger::ERROR]);
        $this->assertCount(1, $errorMessages, '$errorMessages = ' . print_r($errorMessages, true));
        $this->assertStringContainsString('Could not cache the HTML', $errorMessages[0]);

        // Not expecting any warning messages, though
        $warningMessages = $this->logger->getMessages([Logger::WARNING]);
        $this->assertCount(0, $warningMessages, '$warningMessages = ' . print_r($warningMessages, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        // Clean up
        chmod($cacheFile, 0666);
        unlink($cacheFile);
        unlink($cacheFile . '.lock');
    }

    public function testSuccessCachedFileIsStillFresh(): void
    {
        $jkaServerAddress = 'example.com';
        $cacheFile = $this->cacheDir . '/example-com.html';

        $this->assertFalse(file_exists($cacheFile));

        // Render for the first time => Generates the cache file
        $this->performRenderWithTimeoutStatus($jkaServerAddress, 10);

        $this->assertTrue(file_exists($cacheFile));

        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        // Reset the logger
        $this->logger = new MockLogger();
        
        ///////////////////////
        // Render a second time
        $this->performRenderWithTimeoutStatus($jkaServerAddress, 10);
        
        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('From cache');
        $this->assertDidNotGetInfoMessage('Generating HTML');

        unlink($cacheFile);
        unlink($cacheFile . '.lock');
    }

    public function testSuccessWhenCachedFileIsOutdated(): void
    {
        $jkaServerAddress = 'example.com';
        $cacheFile = $this->cacheDir . '/example-com.html';

        $this->assertFalse(file_exists($cacheFile));

        // Render for the first time => Generates the cache file
        $this->performRenderWithTimeoutStatus($jkaServerAddress, 10);

        $this->assertTrue(file_exists($cacheFile));

        // Set the modification time to 11 seconds ago
        $firstRenderTime = time();
        $this->assertTrue(touch($cacheFile, $firstRenderTime - 11));

        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        // Reset the logger
        $this->logger = new MockLogger();

        ///////////////////////
        // Render a second time
        $this->performRenderWithTimeoutStatus($jkaServerAddress, 10); // 10 second caching delay
        
        $this->assertGreaterThanOrEqual($firstRenderTime, filemtime($cacheFile), 'The cache file was NOT refreshed');

        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        unlink($cacheFile);
        unlink($cacheFile . '.lock');
    }

    public function testSuccessWhenCacheIsDisabled(): void
    {
        $jkaServerAddress = 'example.com';
        $cacheFile = $this->cacheDir . '/example-com.html';

        $this->assertFalse(file_exists($cacheFile));

        $this->performRenderWithTimeoutStatus($jkaServerAddress, 0); // 0 => disables cache

        $this->assertFalse(file_exists($cacheFile));

        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');
    }

    public function testSuccessWhenServerTimesOut(): void
    {
        $cacheFile = $this->cacheDir . '/192-0-2-1.html';
        $this->performRenderWithTimeoutStatus('192.0.2.1');

        // Not expecting any ERROR nor WARNING messages
        $errorsAndWarnings = $this->logger->getMessages([Logger::ERROR, Logger::WARNING]);
        $this->assertCount(0, $errorsAndWarnings, '$errorsAndWarnings = ' . print_r($errorsAndWarnings, true));

        // Expecting an INFO message
        $this->assertGotInfoMessage('Generating HTML');
        $this->assertDidNotGetInfoMessage('From cache');

        unlink($cacheFile);
        unlink($cacheFile . '.lock');
    }

    ////////////////////////////////////////////////////////////////////////////
    // Private methods

    /**
     * Renders a status page with the "Up" status, and performs some checks against the generated HTML.
     * @param string $jkaServerAddress Address of the JKA server (e.g. "example.com")
     * @param int $cachingDelay Caching delay, in seconds
     */
    private function performRenderWithUpStatus(string $jkaServerAddress, int $cachingDelay = 10): void
    {
        $jkaServerConfig = new JkaServerConfigData('/', $jkaServerAddress, 'Example Name', '');

        $canonicalUrl = 'https://example.com';
        $_SERVER['REQUEST_URI'] = '/test';

        $config = new ConfigData(
            jkaServers: [$jkaServerConfig],
            cacheDir: $this->cacheDir,
            cachingDelay: $cachingDelay,
            canonicalUrl: $canonicalUrl,
        );
        $templateHelper = new TemplateHelper($config, $this->logger);

        $serverName = '^1T^7otally ^1L^7egit ^1S^7erver';
        $statusText = 'Up';
        $mapName = 'mp/ffa3';
        $backgroundImageUrl = '/levelshots/mp/ffa3.jpg';
        $gameType = 'FFA';
        $gameName = 'OpenJK';
        $nbPlayers = 13;
        $maxNbPlayers = 32;
        $nbHumans = 11;
        $nbBots = 2;

        $defaultBlurRadius = 0; // For "default.jpg"
        $defaultOpacity = 50; // For "default.jpg"
        $backgroundBlurRadius = 7;
        $backgroundOpacity = 40;

        $players = [
            new PlayerData('^2C3P0 ^0(BOT)', 0, 0),
            new PlayerData('^2R2D2 ^0(BOT)', 1, 0),
            new PlayerData('^2§^0ÑØW^2§^0TØ®M^2¹', 9, 96),
            new PlayerData('Padawan', 0, 93),
            new PlayerData('Padawan 2', 1, 184),
            new PlayerData('Padawan 3', 0, 45),
            new PlayerData('Padawan 4', 3, 77),
            new PlayerData('Padawan 5', 1, 99),
            new PlayerData('Padawan 7', 2, 201),
            new PlayerData('Padawan 8', 0, 82),
            new PlayerData('Padawan 11', 0, 250),
            new PlayerData('Padawan 12', 1, 143),
            new PlayerData('Padawan 13', 0, 102),
        ];
        $cvars = [
            'version' => 'JAmp: v1.0.1.0 win_msvc-x86 Apr 1 2025',
            'protocol' => '26',
            'sv_hostname' => $serverName,
            'timelimit' => '20',
            'fraglimit' => 0,
            'gamename' => $gameName,
            'g_gametype' => 0,
            'mapname' => $mapName,
            'sv_maxclients' => $maxNbPlayers,
        ];

        $statusData = new StatusData(
            $config->isLandingPageEnabled,
            $config->landingPageUri,
            $config->isAboutPageEnabled,
            $config->aboutPageUri,
            $config->aboutPageTitle,
            $serverName,
            $jkaServerConfig->address,
            true, // Is up?
            $statusText,
            $defaultBlurRadius,
            $defaultOpacity,
            $backgroundBlurRadius,
            $backgroundOpacity,
            $backgroundImageUrl,
            $mapName,
            $gameType,
            $gameName,
            $nbPlayers,
            $maxNbPlayers,
            $nbHumans,
            $nbBots,
            $players,
            $cvars,
        );

        // Don't let the JkaServerService send UDP data, return the desired StatusData immediately
        $jkaServerService = $this->createConfiguredStub(
            JkaServerServiceInterface::class,
            ['getStatusData' => $statusData]
        );

        $statusController = new StatusController($jkaServerService, $config, $this->logger, $templateHelper);
        $html = @$statusController->getHtmlStatus($jkaServerConfig);

        // We should get the relevant HTML (it doesn't matter whether the cache works or not)
        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString(
            '<h1>' . $templateHelper->formatName($serverName) . '</h1>',
            $html,
            'Did not find the expected H1 title.'
        );
        $this->assertStringContainsString('Address:', $html);
        $this->assertStringContainsString($jkaServerAddress, $html);

        $this->assertStringContainsString('Status:', $html);
        $this->assertStringContainsString($statusText, $html);

        $this->assertStringContainsString('Map name:', $html);
        $this->assertStringContainsString($mapName, $html);

        $this->assertStringContainsString('Game type:', $html);
        $this->assertStringContainsString($gameType, $html);

        $this->assertStringContainsString('Mod name:', $html);
        $this->assertStringContainsString($templateHelper->formatName($gameName), $html);

        $this->assertStringContainsString('Players:', $html);
        $this->assertMatchesRegularExpression("/$nbPlayers\s*\/\s*$maxNbPlayers/", $html); // e.g. "13 / 32"
        $this->assertStringContainsString("$nbHumans human", $html);

        /////////////////
        // OpenGraph tags

        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:type"', $html);

        // TODO: Parse the HTML properly (not with a regex...)
        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<meta') . '.*' . preg_quote('property="og:url"') . '.*'
                . preg_quote(
                    'content="' . htmlspecialchars($canonicalUrl) . $_SERVER['REQUEST_URI'] . '"',
                    '/' // Also escape the '/' delimiter
                )
                . '/s', // /s = dot also matches newlines
            $html
        );

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<meta') . '.*' . preg_quote('property="og:image"') . '.*'
                . preg_quote(
                    'content="' . htmlspecialchars($canonicalUrl) . '/og-image.jpg',
                    '/' // Also escape the '/' delimiter
                ) . '/s',
                // /s = dot also matches newlines
                // No closing double quote for the "content" attribute, because the URL may have a query string
            $html
        );

        ///////////////////////////////////////
        // Background images and blur / opacity

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="map-background-image"') . '.*'
                . preg_quote(
                    'value="' . htmlspecialchars($backgroundImageUrl),
                    '/' // Also escape the '/' delimiter
                ) . '/s',
                // /s = dot also matches newlines
                // No closing double quote for the "value" attribute, because the URL may have a query string
            $html
        );

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="map-background-image-blur-radius"') . '.*'
                . preg_quote('value="' . $backgroundBlurRadius . '"') . '/s', // s = dot also matches newlines
            $html
        );

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="map-background-image-opacity"') . '.*'
                . preg_quote('value="' . $backgroundOpacity . '"') . '/s', // s = dot also matches newlines
            $html
        );

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="default-background-image"') . '.*'
                . preg_quote(
                    'value="' . htmlspecialchars(StatusData::DEFAULT_BACKGROUND_IMAGE_URL),
                    '/' // Also escape the '/' delimiter
                )
                . '/s',
                // No closing double quote for the "value" attribute, because the URL may have a query string
            $html
        );

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="default-background-image-blur-radius"') . '.*'
                . preg_quote('value="' . $defaultBlurRadius . '"') . '/s', // s = dot also matches newlines
            $html
        );

        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="default-background-image-opacity"') . '.*'
                . preg_quote('value="' . $defaultOpacity . '"') . '/s', // s = dot also matches newlines
            $html
        );

        ////////////////////
        // Players and cvars

        $this->assertStringContainsString('<table class="player-list">', $html); // There are players
        
        foreach ($players as $playerData) {
            $this->assertStringContainsString($templateHelper->formatName($playerData->name), $html);
            $this->assertStringContainsString('<td>' . $playerData->score . '</td>', $html);
            $this->assertStringContainsString('<td>' . $playerData->ping . '</td>', $html);
        }

        foreach ($cvars as $cvarName => $cvarValue) {
            $this->assertStringContainsString('<th>' . $cvarName . '</th>', $html);
            $this->assertStringContainsString('<td>' . $templateHelper->formatName((string)$cvarValue) . '</td>', $html);
        }
    }

    /**
     * Renders a status page with the "Timeout" status, and performs a few basic checks against the generated HTML.
     * @param string $jkaServerAddress Address of the JKA server (e.g. "example.com")
     * @param int $cachingDelay Caching delay, in seconds
     */
    private function performRenderWithTimeoutStatus(string $jkaServerAddress, int $cachingDelay = 10): void
    {
        $jkaServerConfig = new JkaServerConfigData('/', $jkaServerAddress, 'Dummy JKA Server', '');
        $config = new ConfigData(
            jkaServers: [$jkaServerConfig],
            cacheDir: $this->cacheDir,
            cachingDelay: $cachingDelay
        );
        $templateHelper = new TemplateHelper($config, $this->logger);

        $statusData = new StatusData(
            $config->isLandingPageEnabled,
            $config->landingPageUri,
            $config->isAboutPageEnabled,
            $config->aboutPageUri,
            $config->aboutPageTitle,
            $jkaServerConfig->name,
            $jkaServerConfig->address,
            false, // Is up?
            'Timeout', // Status
            0, // Default background blur radius
            50, // Default background blur radius
        );

        // Don't let the JkaServerService send UDP data, return the desired StatusData immediately
        $jkaServerService = $this->createConfiguredStub(
            JkaServerServiceInterface::class,
            ['getStatusData' => $statusData]
        );

        $statusController = new StatusController($jkaServerService, $config, $this->logger, $templateHelper);
        $html = @$statusController->getHtmlStatus($jkaServerConfig);

        // We should get some HTML, even if the JKA server times out
        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString(
            '<h1>' . $templateHelper->formatName($jkaServerConfig->name) . '</h1>',
            $html,
            'Did not find the expected H1 title.'
        );

        $this->assertStringContainsString('Address:', $html);
        $this->assertStringContainsString($jkaServerAddress, $html);

        $this->assertStringContainsString('Status:', $html);
        $this->assertStringContainsString('Timeout', $html);

        // The **map-dependent** background image should also be "default.jpg" (because there's no map)
        $this->assertMatchesRegularExpression(
            '/' . preg_quote('<input') . '.*' . preg_quote('id="map-background-image"') . '.*'
                . preg_quote('value="' . StatusData::DEFAULT_BACKGROUND_IMAGE_URL , '/') . '/s',
                // No closing double quote for the "value" attribute, because the URL may have a query string
            $html
        );

        $this->assertStringNotContainsString('<table class="player-list">', $html); // No players

        // No OpenGraph tags (because we haven't specified a "canonical URL" in the config data)
        $this->assertStringNotContainsString('property="og:title"', $html);
    }

    /**
     * Verifies that the logger caught at least 1 INFO message containing the specified substring.
     * (The logger is allowed to contain other INFO messages).
     */
    private function assertGotInfoMessage(string $expectedString): void
    {
        $infoMessages = $this->logger->getMessages([Logger::INFO]);
        $this->assertGreaterThanOrEqual(1, count($infoMessages));
        $foundExpectedMessage = false;
        foreach ($infoMessages as $message) {
            if (str_contains($message, $expectedString)) {
                $foundExpectedMessage = true;
                break;
            }
        }
        $this->assertTrue(
            $foundExpectedMessage,
            'Did not find the expected INFO log message ("' . $expectedString . '").'
        );
    }

    /**
     * Verifies that the logger did NOT catch any INFO message containing the specified substring.
     */
    private function assertDidNotGetInfoMessage(string $subString): void
    {
        $infoMessages = $this->logger->getMessages([Logger::INFO]);
        $foundMessage = false;
        foreach ($infoMessages as $message) {
            if (str_contains($message, $subString)) {
                $foundMessage = true;
                break;
            }
        }
        $this->assertFalse($foundMessage, 'Found the message we did NOT want ("' . $subString . '").');
    }
}
