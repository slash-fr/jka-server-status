<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\JkaServer;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Config\JkaServerConfigData;
use JkaServerStatus\JkaServer\JkaServerResponse;
use JkaServerStatus\JkaServer\JkaServerResponseStatus;
use JkaServerStatus\JkaServer\JkaServerService;
use JkaServerStatus\JkaServer\StatusData;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Template\TemplateHelper;
use JkaServerStatus\Tests\Log\MockLogger;
use JkaServerStatus\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class JkaServerServiceTest extends TestCase
{
    // Actually valid cvars, found in the wild
    // Array of strings (no int, or anything else)
    private const CVARS = [
        'version' => 'JAmp: v1.0.1.0 win_msvc-x86 May  2 2024',
        'timelimit' => '45',
        'sv_privateClients' => '0',
        'sv_minRate' => '4000',
        'sv_minPing' => '0',
        'sv_maxclients' => '32',
        'sv_maxRate' => '90000',
        'sv_maxPing' => '999',
        'sv_httpdownloads' => '0',
        // Servers typically don't use UTF-8, but 8-bit encodings such as Windows-1252
        'sv_hostname' => "\x80\x80\x80\x80^5M^7ystic^5F^7orces^5.net - ^5M^7ystic ^5L^7ugormod",
        'sv_fps' => '40',
        'sv_floodProtectSlow' => '1',
        'sv_floodProtect' => '1',
        'sv_autoDemo' => '0',
        'sv_allowDownload' => '0',
        'serveruptime' => '3',
        'protocol' => '26',
        'mapname' => 'mp/siege_korriban',
        'gamename' => '^5Mystic Lugormod',
        'g_weaponDisable' => '0',
        'g_stepSlideFix' => '1',
        'g_siegeTeamSwitch' => '1',
        'g_siegeTeam2' => 'none',
        'g_siegeTeam1' => 'none',
        'g_siegeRespawn' => '20',
        'g_showDuelHealths' => '1',
        'g_saberWallDamageScale' => '0.4',
        'g_saberLocking' => '0',
        'g_privateDuel' => '255',
        'g_noSpecMove' => '1',
        'g_needpass' => '0',
        'g_maxHolocronCarry' => '3',
        'g_maxGameClients' => '0',
        'g_maxForceRank' => '0',
        'g_jediVmerc' => '0',
        'g_gametype' => '0',
        'g_gameMode' => '0',
        'g_forceRegenTime' => '40',
        'g_forcePowerDisable' => '0',
        'g_forceBasedTeams' => '1',
        'g_duelWeaponDisable' => '0',
        'g_debugMelee' => '1',
        'g_allownpc' => '1',
        'fraglimit' => '0',
        'duel_fraglimit' => '0',
        'dmflags' => '128',
        'capturelimit' => '0',
        'bot_minplayers' => '29',
        'bg_fighterAltControl' => '0',
        'MysticLMD_Version' => '1.0.3',
    ];

    private const CONFIG_SERVER_ADDRESS = 'example.com';
    private const CONFIG_SERVER_NAME = '^1E^7xample ^1S^7erver';
    private const CONFIG_SERVER_CHARSET = 'Windows-1252';

    private readonly MockLogger $logger;
    private readonly JkaServerConfigData $jkaServerConfig;
    private readonly JkaServerService $jkaServerService;

    public function setUp(): void
    {
        parent::setUp();

        $this->jkaServerConfig = new JkaServerConfigData(
            '/', // URI
            self::CONFIG_SERVER_ADDRESS,
            self::CONFIG_SERVER_NAME,
            '', // The subtitle isn't used in the StatusData object
            self::CONFIG_SERVER_CHARSET,
        );

        $config = new ConfigData(
            10, // Caching delay
            3, // Timeout delay
            '/prefix', // Root URL (prefix)
            true, // Enable the landing page
            '/home', // Landing page URL
            true, // Enable the "About" page
            '/tell-me-about-it', // "About" page URI
            'Credits (and legal stuff)', // "About" page title
            [$this->jkaServerConfig]
        );

        $this->logger = new MockLogger();

        $this->jkaServerService = new JkaServerService(
            $config,
            $this->logger,
            new TemplateHelper($config, $this->logger)
        );
    }

    public function testBuildFullUdpUrl(): void
    {
        $this->assertSame('udp://192.0.2.1:29070', JkaServerService::buildFullUdpUrl('192.0.2.1'));
        $this->assertSame('udp://192.0.2.1:29071', JkaServerService::buildFullUdpUrl('192.0.2.1:29071'));
        $this->assertSame('udp://jka.example.com:29070', JkaServerService::buildFullUdpUrl('jka.example.com'));
        $this->assertSame('udp://jka.example.com:29071', JkaServerService::buildFullUdpUrl('jka.example.com:29071'));
    }

    /**
     * Checks data that should always match - no matter if it's a success, timeout, ...
     * @param StatusData $statusData Status Data returned by JkaServerService::buildStatusData()
     */
    private function assertBasicStatusDataIsValid(StatusData $statusData): void
    {
        $this->assertTrue($statusData->isLandingPageEnabled);
        $this->assertSame('/home', $statusData->landingPageUri);
        $this->assertTrue($statusData->isAboutPageEnabled);
        $this->assertSame('/tell-me-about-it', $statusData->aboutPageUri);
        $this->assertSame('Credits (and legal stuff)', $statusData->aboutPageTitle);
        $this->assertSame(self::CONFIG_SERVER_ADDRESS, $statusData->address);
        $this->assertSame('/levelshots/default.jpg', $statusData->defaultBackgroundImageUrl);
        // The background image URIs are NOT prefixed by the "root URL" at this point.
        // The asset() function will do that in the templates.
        $this->assertIsArray($statusData->cvars);
    }

    /**
     * Verifies that the data matches an error (Down, Timeout, ...)
     * @param StatusData $statusData Status Data returned by JkaServerService::buildStatusData()
     * @param string $expectedStatusMessage e.g. "Down", "Timeout", ...
     */
    private function assertIsNotUp(StatusData $statusData, string $expectedStatusMessage): void
    {
        $this->assertFalse($statusData->isUp);
        $this->assertSame($expectedStatusMessage, $statusData->status);
        $this->assertSame(self::CONFIG_SERVER_NAME, $statusData->serverName); // Should match the config
        // No cvars
        $this->assertSame(0, count($statusData->cvars));
        $this->assertNull($statusData->gameName);
        $this->assertNull($statusData->gameType);
        $this->assertNull($statusData->mapName);
        $this->assertNull($statusData->maxPlayers);
        // No players
        $this->assertNull($statusData->nbPlayers);
        $this->assertNull($statusData->nbBots);
        $this->assertNull($statusData->nbHumans);
        $this->assertIsArray($statusData->players);
        $this->assertSame(0, count($statusData->players));
        // No map => default background
        $this->assertSame('/levelshots/default.jpg', $statusData->backgroundImageUrl);
        // The background image URIs are NOT prefixed by the "root URL" at this point.
        // The asset() function will do that in the templates.
    }

    public function testBuildStatusDataWithTimeout(): void
    {
        $jkaServerResponse = new JkaServerResponse(JkaServerResponseStatus::Timeout, '');

        $statusData = $this->jkaServerService->buildStatusData($this->jkaServerConfig, $jkaServerResponse);

        $this->assertBasicStatusDataIsValid($statusData);
        $this->assertIsNotUp($statusData, 'Timeout');
        
        // Check the log messages:
        $errorMessages = $this->logger->getMessages([Logger::ERROR]);
        $this->assertCount(1, $errorMessages);
        $this->assertSame(self::CONFIG_SERVER_ADDRESS . ' - Status: Timeout', $errorMessages[0]);
        $this->assertCount(0, $this->logger->getMessages([Logger::WARNING]));
    }

    public function testBuildStatusDataWithNetworkError(): void
    {
        $jkaServerResponse = new JkaServerResponse(JkaServerResponseStatus::NetworkError, '');

        $statusData = $this->jkaServerService->buildStatusData($this->jkaServerConfig, $jkaServerResponse);

        $this->assertBasicStatusDataIsValid($statusData);
        $this->assertIsNotUp($statusData, 'Down');
        
        // Check the log messages:
        $errorMessages = $this->logger->getMessages([Logger::ERROR]);
        $this->assertCount(1, $errorMessages);
        $this->assertSame(self::CONFIG_SERVER_ADDRESS . ' - Status: Down', $errorMessages[0]);
        $this->assertCount(0, $this->logger->getMessages([Logger::WARNING]));
    }

    public static function invalidResponseDataProvider()
    {
        // Empty:
        yield [''];

        // Just a newline:
        yield ["\n"];

        // Garbage data:
        yield ['A møøse once bit my sister…'];

        // Missing cvars:
        yield [
            "\xFF\xFF\xFF\xFFstatusResponse\n"
            . '0 0 "^2E^7arth^0-Bot"' . "\n"
            . '0 0 "^3A^7ncient^0-Bot"' . "\n"
            . '0 0 "^5M^7ystic^0-Bot"' . "\n"
        ];

        // Missing the 4 starting "\xFF" bytes:
        yield [
            "statusResponse\n"
            . self::getCvarLine() . "\n"
            . '0 0 "^2E^7arth^0-Bot"' . "\n"
            . '0 0 "^3A^7ncient^0-Bot"' . "\n"
            . '0 0 "^5M^7ystic^0-Bot"' . "\n"
        ];
    }

    #[DataProvider('invalidResponseDataProvider')]
    public function testBuildStatusDataWithInvalidResponse(string $response): void
    {
        $jkaServerResponse = new JkaServerResponse(
            JkaServerResponseStatus::Success, // Successful network request, but...
            $response // ...invalid data
        );

        $statusData = $this->jkaServerService->buildStatusData($this->jkaServerConfig, $jkaServerResponse);

        $this->assertBasicStatusDataIsValid($statusData);
        $this->assertIsNotUp($statusData, 'Invalid response');
        
        // Check the log messages:
        $errorMessages = $this->logger->getMessages([Logger::ERROR]);
        $this->assertCount(1, $errorMessages);
        $this->assertSame(self::CONFIG_SERVER_ADDRESS . ' - Status: Invalid response', $errorMessages[0]);
        $this->assertCount(0, $this->logger->getMessages([Logger::WARNING]));
    }

    public static function validResponseDataProvider()
    {
        ////////////////////////////////////////////////////////////////////////
        // 1. Typical response:

        // Using a plain old indexed array for player data, rather than a PlayerData object,
        // because we need to detect any bugs in the PlayerData object (in addition to StatusData)
        $playerData = [
            // Special characters (Windows-1252):
            ['score' => 12, 'ping' => 101, 'name' => iconv('UTF-8', 'Windows-1252//IGNORE', '^2§^0ÑØW^2§^0TØ®M^2¹')],
            ['score' => 3, 'ping' => 248, 'name' => '^8J^7ake'], // Lower score than the previous player
            ['score' => 3, 'ping' => 188, 'name' => '^7fi^1N^7t'], // Same score, lower ping
            ['score' => 0, 'ping' => 98, 'name' => 'Padawan 3'], // Lower score
            ['score' => 0, 'ping' => 42, 'name' => 'Padawan 1'], // Same score, lower ping
            ['score' => 0, 'ping' => 42, 'name' => 'Padawan 2'], // Same score, same ping, higher alphabetical order
            ['score' => 0, 'ping' => 0, 'name' => '^5M^7ystic^0-Bot'], // Same score, lower ping
            ['name' => '^4S^7las^1h', 'score' => -2, 'ping' => 178], // Lower score (negative score, even)
        ];
        // In the end, players should be sorted by: score (desc), then ping (desc), then name (asc)
        // But the server typically sends them ordered by client ID
        $response = "\xFF\xFF\xFF\xFFstatusResponse\n"
            . self::getCvarLine() . "\n"
            . $playerData[6]['score'] . ' ' . $playerData[6]['ping'] . ' "' . $playerData[6]['name'] . '"' . "\n" // Mystic-Bot
            . $playerData[2]['score'] . ' ' . $playerData[2]['ping'] . ' "' . $playerData[2]['name'] . '"' . "\n" // fiNt
            . $playerData[1]['score'] . ' ' . $playerData[1]['ping'] . ' "' . $playerData[1]['name'] . '"' . "\n" // Jake
            . $playerData[0]['score'] . ' ' . $playerData[0]['ping'] . ' "' . $playerData[0]['name'] . '"' . "\n" // Snowstorm
            . $playerData[7]['score'] . ' ' . $playerData[7]['ping'] . ' "' . $playerData[7]['name'] . '"' . "\n" // Slash
            . $playerData[4]['score'] . ' ' . $playerData[4]['ping'] . ' "' . $playerData[4]['name'] . '"' . "\n" // Padawan 1
            . $playerData[5]['score'] . ' ' . $playerData[5]['ping'] . ' "' . $playerData[5]['name'] . '"' . "\n" // Padawan 2
            . $playerData[3]['score'] . ' ' . $playerData[3]['ping'] . ' "' . $playerData[3]['name'] . '"' . "\n" // Padawan 3
        ;

        // $response, $expectedNbPlayers, $expectedNbBots, $expectedNbHumans, $expectedPlayerData, $expectedWarnings
        yield [$response, 8, 1, 7, $playerData, []];
        
        ////////////////////////////////////////////////////////////////////////
        // 2. No empty last line:

        $playerData = [
            ['score' => 0, 'ping' => 0, 'name' => '^3A^7ncient^0-Bot'],
            // Color codes should be ignored when sorting in alphabetical order
            ['score' => 0, 'ping' => 0, 'name' => '^2E^7arth^0-Bot'], // Same score/ping, higher alphabetical order
            ['score' => 0, 'ping' => 0, 'name' => '^5M^7ystic^0-Bot'], // Same score/ping, higher alphabetical order
        ];
        $response = "\xFF\xFF\xFF\xFFstatusResponse\n"
            . self::getCvarLine() . "\n"
            . $playerData[1]['score'] . ' ' . $playerData[1]['ping'] . ' "' . $playerData[1]['name'] . '"' . "\n" // Earth-Bot
            . $playerData[0]['score'] . ' ' . $playerData[0]['ping'] . ' "' . $playerData[0]['name'] . '"' . "\n" // Ancient-Bot
            . $playerData[2]['score'] . ' ' . $playerData[2]['ping'] . ' "' . $playerData[2]['name'] . '"'; // Mystic-Bot
        // Does not end with "\n"
        // Not sure it's valid, but we'll accept it anyway.

        // $response, $expectedNbPlayers, $expectedNbBots, $expectedNbHumans, $expectedPlayerData, $expectedWarnings
        yield [$response, 3, 3, 0, $playerData, []];

        ////////////////////////////////////////////////////////////////////////
        // 3. Invalid player lines
        // buildStatusData() should still work for the other players, and the cvars, but it should generate warnings

        $playerData = [
            ['score' => 1, 'ping' => 98, 'name' => 'Padawan 2'],
            ['score' => 1, 'ping' => 42, 'name' => 'Padawan 1'], // Same score, lower ping
        ];
        $response = "\xFF\xFF\xFF\xFFstatusResponse\n"
            . self::getCvarLine() . "\n"
            . $playerData[1]['score'] . ' ' . $playerData[1]['ping'] . ' "' . $playerData[1]['name'] . '"' . "\n" // Padawan 1
            . '"Padawan 3"' . "\n" // No score and no ping => should generate a warning
            . '10 "Padawan 4"' . "\n" // No score (or no ping) => should generate a warning
            . '10 148' . "\n" // No name => should generate a warning
            . $playerData[0]['score'] . ' ' . $playerData[0]['ping'] . ' "' . $playerData[0]['name'] . '"' . "\n" // Padawan 2
        ;

        $expectedWarnings = [
            self::CONFIG_SERVER_ADDRESS . ' - The server response contains an invalid player line',
            self::CONFIG_SERVER_ADDRESS . ' - The server response contains an invalid player line',
            self::CONFIG_SERVER_ADDRESS . ' - The server response contains an invalid player line',
        ];

        // $response, $expectedNbPlayers, $expectedNbBots, $expectedNbHumans, $expectedPlayerData, $expectedWarnings
        yield [$response, 2, 0, 2, $playerData, $expectedWarnings];

    }

    #[DataProvider('validResponseDataProvider')]
    public function testBuildStatusDataWithValidResponse(
        string $response,
        int $expectedNbPlayers,
        int $expectedNbBots,
        int $expectedNbHumans,
        array $expectedPlayerData,
        array $expectedWarnings
    ): void
    {
        $jkaServerResponse = new JkaServerResponse(
            JkaServerResponseStatus::Success, // Successful network request
            $response // Valid data
        );

        $statusData = $this->jkaServerService->buildStatusData($this->jkaServerConfig, $jkaServerResponse);

        $this->assertTrue($statusData->isUp);
        $this->assertSame('Up', $statusData->status);

        $this->assertBasicStatusDataIsValid($statusData);

        // buildStatusData should strip the leading "\x80" characters
        $this->assertSame('^5M^7ystic^5F^7orces^5.net - ^5M^7ystic ^5L^7ugormod', $statusData->serverName);
        $this->assertSame('/levelshots/' . self::CVARS['mapname'] . '.jpg', $statusData->backgroundImageUrl);
        
        // cvars
        $this->assertSame(count(self::CVARS), count($statusData->cvars));
        $this->assertSame(self::CVARS['gamename'], $statusData->gameName);
        $this->assertSame('FFA', $statusData->gameType);
        $this->assertSame(self::CVARS['mapname'], $statusData->mapName);
        $this->assertSame((int)self::CVARS['sv_maxclients'], $statusData->maxPlayers);
        foreach (self::CVARS as $key => $value) {
            $this->assertSame(
                iconv('Windows-1252', 'UTF-8', $value), // Convert back to UTf-8
                $statusData->cvars[$key] ?? '', // StatusData uses UTF-8
                'Unexpected value for the "' . $key . '" cvar.'
            );
        }

        // Player data
        $this->assertSame($expectedNbBots, $statusData->nbBots);
        $this->assertSame($expectedNbHumans, $statusData->nbHumans);
        $this->assertSame($expectedNbPlayers, $statusData->nbPlayers);
        $this->assertIsArray($statusData->players);
        $this->assertSame($expectedNbPlayers, count($statusData->players));
        foreach ($expectedPlayerData as $index => $playerDataArray) {
            // Using a plain old indexed array for $playerDataArray, rather than a PlayerData object,
            // because we need to detect any bugs in the PlayerData object (in addition to StatusData)
            $this->assertSame(
                iconv('Windows-1252', 'UTF-8', $playerDataArray['name']), // Convert back to UTf-8
                $statusData->players[$index]->name, // StatusData uses UTF-8
            );
            $this->assertSame($playerDataArray['score'], $statusData->players[$index]->score);
            $this->assertSame($playerDataArray['ping'], $statusData->players[$index]->ping);
        }

        $this->assertCount(0, $this->logger->getMessages([Logger::ERROR]));
        $warningMessages = $this->logger->getMessages([Logger::WARNING]);
        $this->assertCount(count($expectedWarnings), $warningMessages);
        foreach ($expectedWarnings as $index => $expectedWarningMessage) {
            $this->assertStringStartsWith($expectedWarningMessage, $warningMessages[$index]);
        }
    }

    /**
     * Gets a line with actually valid cvars. Does not end with a newline character.
     */
    private static function getCvarLine(): string
    {
        $cvars = '';
        foreach (self::CVARS as $key => $value) {
            $cvars .= "\\$key\\$value";
        }

        return $cvars;
    }
}