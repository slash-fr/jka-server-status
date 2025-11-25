<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

/**
 * DTO for the JKA server status data
 */
class StatusData
{
    public const DEFAULT_BACKGROUND_IMAGE_URL = '/levelshots/default.jpg';

    public readonly bool $isLandingPageEnabled;
    public readonly string $landingPageUri;
    public readonly bool $isAboutPageEnabled;
    public readonly string $aboutPageUri;
    public readonly string $aboutPageTitle;

    /**
     * @var string $serverName Server name, with color codes
     */
    public readonly string $serverName;

    public readonly string $address;

    public readonly bool $isUp;

    public readonly string $status;

    /**
     * @var string $backgroundImageUrl Map-dependent background image URL. Root-relative.
     * WITHOUT $asset_url prefix.
     * Please call TemplateHelper::asset() on it to get the correct path, with the cache busting query string.
     */
    public readonly string $backgroundImageUrl;

    /**
     * @var int $backgroundImageOpacity Opacity for the map-dependent background image (e.g. 50)
     */
    public readonly int $backgroundImageOpacity;

    /** 
     * @var string|null $mapName Map name (e.g. "mp/ffa3").
     * Might not be set (e.g. if the server is down, or doesn't give the info, for some reason)
     */
    public readonly ?string $mapName;

    /** 
     * @var string|null $gameType Game type (e.g. "FFA"). Might not be set.
     */
    public readonly ?string $gameType;

    /** 
     * @var string|null $gameName Mod name ("gamename" cvar, e.g. "Lugormod"). Might not be set.
     */
    public readonly ?string $gameName;

    /**
     * @var int|null $nbPlayers Number of players. Might not be set.
     */
    public readonly ?int $nbPlayers;

    /**
     * @var int|null $maxPlayers Max number of players ("sv_maxclients" cvar). Might not be set.
     */
    public readonly ?int $maxPlayers;

    /**
     * @var int|null $nbHumans Number of human players. Might not be set.
     */
    public readonly ?int $nbHumans;

    /**
     * @var int|null $nbBots Number of bots. Might not be set.
     */
    public readonly ?int $nbBots;

    /**
     * @var PlayerData[] $players
     */
    public readonly array $players;

    /**
     * @var string[] $cvars Indexed array: "cvar_name" => "cvar_value"
     */
    public readonly array $cvars;

    /**
     * @param bool $isLandingPageEnabled Is the landing page enabled?
     * @param string $landingPageUri Landing page URI (e.g. '/')
     * @param bool $isAboutPageEnabled Is the "About" page enabled?
     * @param string $aboutPageUri URI of the "About" page (e.g. '/about')
     * @param string $aboutPageTitle Title of the "About" page (e.g. 'About')
     * @param string $serverName Server name, with color codes
     * @param string $address Server IP address or domain name (with optional port)
     * @param bool $isUp Is the server up?
     * @param string $status Status string (e.g. 'Up', 'Down', 'Timeout', ...)
     * @param int $backgroundImageOpacity Opacity for the map-dependent background image (e.g. 50).
     * @param string $backgroundImageUrl URL of the background image (e.g. '/levelshots/mp/ffa3.jpg').
     *                                   If not set, defaults to 'default.jpg'.
     *                                   Always set this, unless there's no map (server down, timeout, ...)
     * @param string|null $mapName Map name (e.g. "mp/ffa3") if available
     * @param string|null $gameType Game type (e.g. "FFA") if available
     * @param string|null $gameName Mod name ("gamename" cvar, e.g. "Lugormod")
     * @param int|null $nbPlayers Number of players, if available
     * @param int|null $maxPlayers Max number of players ("sv_maxclients" cvar) if available
     * @param int|null $nbHumans Number of human players, if available
     * @param int|null $nbBots Number of bots, if available
     * @param PlayerData[] $players
     * @param string[] $cvars Indexed array: "cvar_name" => "cvar_value"
     */
    public function __construct(
        bool $isLandingPageEnabled,
        string $landingPageUri,
        bool $isAboutPageEnabled,
        string $aboutPageUri,
        string $aboutPageTitle,
        string $serverName,
        string $address,
        bool $isUp,
        string $status,
        int $backgroundImageOpacity,
        string $backgroundImageUrl = self::DEFAULT_BACKGROUND_IMAGE_URL,
        ?string $mapName = null,
        ?string $gameType = null,
        ?string $gameName = null,
        ?int $nbPlayers = null,
        ?int $maxPlayers = null,
        ?int $nbHumans = null,
        ?int $nbBots = null,
        array $players = [],
        array $cvars = []
    )
    {
        $this->isLandingPageEnabled = $isLandingPageEnabled;
        $this->landingPageUri = $landingPageUri;
        $this->isAboutPageEnabled = $isAboutPageEnabled;
        $this->aboutPageUri = $aboutPageUri;
        $this->aboutPageTitle = $aboutPageTitle;
        $this->serverName = $serverName;
        $this->address = $address;
        $this->isUp = $isUp;
        $this->status = $status;
        $this->backgroundImageOpacity = $backgroundImageOpacity;
        $this->backgroundImageUrl = $backgroundImageUrl;
        $this->mapName = $mapName;
        $this->gameType = $gameType;
        $this->gameName = $gameName;
        $this->nbPlayers = $nbPlayers;
        $this->maxPlayers = $maxPlayers;
        $this->nbHumans = $nbHumans;
        $this->nbBots = $nbBots;
        $this->players = $players;
        $this->cvars = $cvars;
    }
}
