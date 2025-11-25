<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Config\JkaServerConfigData;
use JkaServerStatus\Log\LoggerInterface;
use JkaServerStatus\Util\Charset;
use JkaServerStatus\Util\Url;

final class JkaServerService implements JkaServerServiceInterface
{
    private const GAME_TYPES = [
        0 => 'FFA',
        1 => 'Holocron FFA',
        2 => 'Jedi Master',
        3 => 'Duel',
        4 => 'Power Duel',
        5 => 'Single Player FFA',
        6 => 'Team FFA',
        7 => 'Siege',
        8 => 'CTF (Capture The Flag)',
        9 => 'CTY (Capture The Ysalamiri)',
    ];

    private readonly ConfigData $config;
    private readonly LoggerInterface $logger;

    public function __construct(ConfigData $config, LoggerInterface $logger) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getStatusData(JkaServerConfigData $jkaServerConfig): StatusData
    {
        $jkaServerResponse = $this->queryJkaServer($jkaServerConfig->address);
        return $this->buildStatusData($jkaServerConfig, $jkaServerResponse);
    }

    /**
    * Send a request to the JKA server, determine whether it was successful, and return the response.
    * @param string $host JKA server IP or hostname, with optional port (defaults to 29070)
    *                     -> e.g. "192.0.2.1", "example.com", "example.com:29070"
    * @return JkaServerResponse
    */
    private function queryJkaServer(string $host): JkaServerResponse
    {
        $url = Url::buildFullUdpUrl($host);
    
        // 3 second timeout for the connect() system call (shouldn't be a problem for a UDP socket)
        $socket = stream_socket_client($url, $error_code, $error_message, 3.0);
        if (!$socket) {
            $this->logger->error("$host - Socket error - Error code: $error_code - Error message: $error_message");
            return new JkaServerResponse(JkaServerResponseStatus::NetworkError);
        }
    
        // Timeout for reading over the socket
        stream_set_timeout($socket, $this->config->timeoutDelay);
    
        if (!fwrite($socket, "\xFF\xFF\xFF\xFFgetstatus\n")) {
            $this->logger->error("$host - Could not write to the UDP socket.");
            return new JkaServerResponse(JkaServerResponseStatus::NetworkError);
        }

        $response = fread($socket, 65535);
        if (!$response) {
            $metadata = stream_get_meta_data($socket);
            fclose($socket);
            if ($metadata['timed_out']) {
                return new JkaServerResponse(JkaServerResponseStatus::Timeout);
            }
            return new JkaServerResponse(JkaServerResponseStatus::NetworkError);
        }
    
        fclose($socket);
    
        // DEBUG: Uncomment the following line to dump the raw response to disk
        //file_put_contents(__DIR__ . '/../../var/log/' . date('Y-m-d_H-i-s') . '_raw_response.txt', $response);

        return new JkaServerResponse(JkaServerResponseStatus::Success, $response);
    }

    /**
     * @inheritdoc
     */
    public function buildStatusData(
        JkaServerConfigData $jkaServerConfig,
        JkaServerResponse $jkaServerResponse,
    ): StatusData
    {
        if ($jkaServerResponse->status === JkaServerResponseStatus::Timeout) {
            $statusMessage = 'Timeout';
            $this->logger->error($jkaServerConfig->address . ' - Status: ' . $statusMessage);
            return new StatusData(
                $this->config->isLandingPageEnabled,
                $this->config->landingPageUri,
                $this->config->isAboutPageEnabled,
                $this->config->aboutPageUri,
                $this->config->aboutPageTitle,
                $jkaServerConfig->name,
                $jkaServerConfig->address,
                false, // isUp
                $statusMessage,
                $this->config->getBackgroundBlurRadius('default'),
                $this->config->getBackgroundOpacity('default'),
            );
        }

        if ($jkaServerResponse->status !== JkaServerResponseStatus::Success) {
            $statusMessage = 'Down';
            $this->logger->error($jkaServerConfig->address . ' - Status: ' . $statusMessage);
            return new StatusData(
                $this->config->isLandingPageEnabled,
                $this->config->landingPageUri,
                $this->config->isAboutPageEnabled,
                $this->config->aboutPageUri,
                $this->config->aboutPageTitle,
                $jkaServerConfig->name,
                $jkaServerConfig->address,
                false, // isUp
                $statusMessage,
                $this->config->getBackgroundBlurRadius('default'),
                $this->config->getBackgroundOpacity('default'),
            );
        }

        // Make sure line endings are only "\n"
        $response = str_replace("\r", "", $jkaServerResponse->data);

        // Parse the output
        $lines = explode("\n", $response);
        $nbLines = count($lines);
        if (
            $nbLines < 2
            || $lines[0] !== "\xFF\xFF\xFF\xFFstatusResponse"
            || !str_starts_with($lines[1], "\\")
        ) {
            $statusMessage = 'Invalid response';
            $this->logger->error($jkaServerConfig->address . ' - Status: ' . $statusMessage);
            return new StatusData(
                $this->config->isLandingPageEnabled,
                $this->config->landingPageUri,
                $this->config->isAboutPageEnabled,
                $this->config->aboutPageUri,
                $this->config->aboutPageTitle,
                $jkaServerConfig->name,
                $jkaServerConfig->address,
                false, // isUp
                $statusMessage,
                $this->config->getBackgroundBlurRadius('default'),
                $this->config->getBackgroundOpacity('default'),
            );
        }

        // Fix the encoding
        for ($i = 1; $i < $nbLines; $i++) {
            $lines[$i] = (string)Charset::toUtf8($lines[$i], $jkaServerConfig->charset);
        }

        // Cvars (e.g. "\key1\value1\key2\value2..." => ["key1" => "value1", "key2" => "value2"])
        $cvars = $this->getCvarsFromRawData($lines[1]);

        // Players
        $players = $this->getPlayers($lines, $jkaServerConfig->address);
        
        // Count players, bots and humans
        $nbPlayers = $nbBots = $nbHumans = 0;
        $this->countPlayers($players, $nbPlayers, $nbBots, $nbHumans);

        $backgroundImageUrl = '';
        $backgroundImageBlurRadius = ConfigData::DEFAULT_BACKGROUND_BLUR_RADIUS;
        $backgroundImageOpacity = ConfigData::DEFAULT_BACKGROUND_OPACITY;
        $this->initializeBackgroundImageSettings(
            $cvars, // Input parameter
            $backgroundImageUrl, // Output parameter
            $backgroundImageBlurRadius, // Output parameter
            $backgroundImageOpacity, // Output parameter
        );

        return new StatusData(
            $this->config->isLandingPageEnabled,
            $this->config->landingPageUri,
            $this->config->isAboutPageEnabled,
            $this->config->aboutPageUri,
            $this->config->aboutPageTitle,
            $this->getServerName($jkaServerConfig, $cvars),
            $jkaServerConfig->address,
            true, // isUp
            'Up',
            $backgroundImageBlurRadius,
            $backgroundImageOpacity,
            $backgroundImageUrl,
            $cvars['mapname'] ?? null,
            $this->getGameType($cvars),
            $cvars['gamename'] ?? null,
            $nbPlayers,
            $this->getMaxPlayers($cvars),
            $nbHumans,
            $nbBots,
            $players,
            $cvars
        );
    }

    /**
     * Parses the cvars from the raw server info
     * @param string $rawCvarData The line with just the cvars (2nd line, e.g. "\key1\value1\key2\value2...")
     * @return array e.g. ["key1" => "value1", "key2" => "value2"]
     */
    private function getCvarsFromRawData(string $rawCvarData): array
    {
        $cvars = [];
        $rawServerArray = explode("\\", $rawCvarData);
        // e.g. ["", "key1", "value1", "key2", "value2"]
        array_shift($rawServerArray); // Ignore the starting backslash
        $nbFields = floor(count($rawServerArray) / 2);
        for ($i = 0; $i < $nbFields; $i++) {
            $name = $rawServerArray[2 * $i];
            $value = $rawServerArray[2 * $i + 1];
            $cvars[$name] = $value;
        }

        // Sort cvars by cvar name
        ksort($cvars, SORT_NATURAL | SORT_FLAG_CASE); // Sort by keys (case insensitive)

        return $cvars;
    }

    /**
     * Gets the server name, from the cvars if possible, from the config otherwise
     * @param array $cvars e.g. ["g_gametype" => "0", "mapname" => "mp/ffa3", "sv_hostname" => "Mystic Lugormod"]
     * @return string The server name (e.g. "Mystic Lugormod")
     */
    private function getServerName(JkaServerConfigData $jkaServerConfig, array $cvars): string
    {
        $serverName = $cvars['sv_hostname'] ?? $jkaServerConfig->name;
        $x80 = Charset::toUtf8("\x80", $jkaServerConfig->charset);
        if ($x80) {
            // "Fix" the server name
            $serverName = ltrim($serverName, $x80);
            // Some server owners prepend "\x80" bytes to the "sv_hostname" cvar,
            // ("\x80" is a euro sign in Windows-1252 encoding),
            // to get their server displayed at the top of the list.
        }

        return $serverName;
    }

    /**
     * Gets the game type as string
     * @param array $cvars e.g. ["g_gametype" => "0", "mapname" => "mp/ffa3", "sv_hostname" => "Mystic Lugormod"]
     * @return string The game type (e.g. "FFA")
     */
    private function getGameType(array $cvars): ?string
    {
        $gameType = null;
        if (isset($cvars['g_gametype']) && isset(self::GAME_TYPES[(int)$cvars['g_gametype']])) {
            // Readable name for the game type
            $gameType = self::GAME_TYPES[(int)$cvars['g_gametype']];
        }

        return $gameType;
    }

    /**
     * Initializes the background image settings from the map name (if present in the cvars).
     * @param array $cvars e.g. ["g_gametype" => "0", "mapname" => "mp/ffa3", "sv_hostname" => "Mystic Lugormod"]
     * @param string $backgroundImageUrl Output parameter. The map-dependent (root-relative) background image URL
     *                                   WITHOUT $asset_url prefix (e.g. "/levelshots/mp/ffa3.jpg")
     * @param int $backgroundImageBlurRadius Output parameter. The blur radius, in pixels,
     *                                       to apply to the background image (e.g. 5)
     * @param int $backgroundImageOpacity Output parameter. The opacity percentage to apply to the background image
     *                                    (e.g. 50)
     */
    private function initializeBackgroundImageSettings(
        array $cvars,
        string &$backgroundImageUrl,
        int &$backgroundImageBlurRadius,
        int &$backgroundImageOpacity,
    ): void
    {
        $mapName = strtolower($cvars['mapname'] ?? 'default');
        if (
            preg_match('/^[a-zA-z_0-9\/]+$/', $mapName) // If the file name is safe (no "..", no weird characters)
            && file_exists($this->config->projectDir . '/public/levelshots/' . $mapName . '.jpg') // and the file exists
        ) {
            $backgroundImageUrl = '/levelshots/' . $mapName . '.jpg';
            $backgroundImageBlurRadius = $this->config->getBackgroundBlurRadius($mapName);
            $backgroundImageOpacity = $this->config->getBackgroundOpacity($mapName);
            return;
        }

        // Otherwise, we can't use the map name as a filename
        $this->logger->warning('Could not find levelshot for "' . $mapName . '". Using "default.jpg".');
        $backgroundImageUrl = StatusData::DEFAULT_BACKGROUND_IMAGE_URL;
        $backgroundImageBlurRadius = $this->config->getBackgroundBlurRadius('default');
        $backgroundImageOpacity = $this->config->getBackgroundOpacity('default');
    }

    /**
     * Parses player data from the response
     * @param string[] $lines Lines from the raw server response
     * @param string $jkaServerAddress JKA Server Address (used in log messages)
     * @return PlayerData[]
     */
    private function getPlayers(array $lines, string $jkaServerAddress): array
    {
        $nbLines = count($lines);

        $players = [];
        for ($i = 2; $i < $nbLines; $i++) {
            if ($i === ($nbLines - 1) && $lines[$i] === '') {
                // The last line is allowed to be empty (without generating a warning)                
                break;
            }

            if (!preg_match('/^(-?[0-9]+)\s+([0-9]+)\s+(.+)$/', $lines[$i], $matches)) {
                $this->logger->warning(
                    $jkaServerAddress . ' - The server response contains an invalid player line: '
                    . var_export($lines[$i], true)
                );
                continue;
            }

            $players[] = new PlayerData(
                trim($matches[3], '"'), // Name
                (int)$matches[1], // Score
                (int)$matches[2], // Ping
            );
        }

        usort($players, function (PlayerData $player1, PlayerData $player2) {
            // Sort by score (descending)
            return (int)$player2->score <=> (int)$player1->score;
        });

        return $players;
    }

    /**
     * Count bots and humans
     * @param PlayerData[] $players
     * @param int $nbPlayers Output parameter: Number of players (bots + humans)
     * @param int $nbBots Output parameter: Number of bots
     * @param int $nbHumans Output parameter: Number of humans
     */
    private function countPlayers(array $players, int &$nbPlayers, int &$nbBots, int &$nbHumans): void
    {
        $nbPlayers = 0;
        $nbBots = 0;
        $nbHumans = 0;
        foreach ($players as $player) {
            $nbPlayers++;
            if (isset($player->ping) && $player->ping === 0) {
                $nbBots++;
            } else {
                $nbHumans++;
            }
        }
    }

    /**
     * Gets the maximum number of players advertised by the server
     * @param array $cvars e.g. ["g_gametype" => "0", "mapname" => "mp/ffa3", "sv_maxclients" => "32"]
     * @return int|null Maximum number of players (e.g. 32). Might not be set.
     */
    private function getMaxPlayers(array $cvars): ?int
    {
        $maxPlayers = null;
        if (isset($cvars['sv_maxclients'])) {
            $maxPlayers = (int)$cvars['sv_maxclients'];
        }

        return $maxPlayers;
    }
}
