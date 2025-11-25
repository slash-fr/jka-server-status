<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

use JkaServerStatus\Config\JkaServerConfigData;

/**
 * General config data. Stores everything except the logging config.
 */
class ConfigData
{
    /**
     * @var int Default caching delay, in seconds.
     */
    public const DEFAULT_CACHING_DELAY = 10;

    /**
     * @var int Default timeout delay, in seconds.
     */
    public const DEFAULT_TIMEOUT_DELAY = 3;

    /**
     * @var string Default URL prefix for the assets (without trailing slash)
     */
    public const DEFAULT_ASSET_URL = '';

    /**
     * @var string Default landing page URI (if enabled)
     */
    public const DEFAULT_LANDING_PAGE_URI = '/';

    /**
     * @var bool Default status of the "About" page (enabled/disabled)
     */
    public const DEFAULT_IS_ABOUT_PAGE_ENABLED = false;

    /**
     * @var string Default URI for the "About" page (if enabled)
     */
    public const DEFAULT_ABOUT_PAGE_URI = '/about';

    /**
     * @var string Default title for the "About" page (and "About" link)
     */
    public const DEFAULT_ABOUT_PAGE_TITLE = 'About';

    /**
     * @var int Background opacity to use when no other value is set. Percentage [0-100].
     */
    public const DEFAULT_BACKGROUND_OPACITY = 50;

    /**
     * @var int[] Background opacity per map / background image. Percentage [0-100].
     */
    public const DEFAULT_BACKGROUND_OPACITY_PER_MAP = [
        'mp/ctf2' => 40,
        'mp/ctf5' => 40,
        'mp/duel6' => 40,
        'mp/duel9' => 40,
        'mp/ffa5' => 40,
        'mp/siege_desert' => 40,
        'mp/siege_hoth' => 40,
        'mp/siege_korriban' => 30,
        'academy3' => 30,
        'academy4' => 30,
        'hoth2' => 40,
        'kor2' => 40,
        't1_sour' => 40,
        't1_surprise' => 40,
        't2_dpred' => 40,
        't2_trip' => 40,
        't2_wedge' => 40,
        't3_hevil' => 40,
        'taspir2' => 40,
        'vjun2' => 40,
        'yavin1' => 40,
        'yavin1b' => 40,
        // Other levelshots default to ConfigData::DEFAULT_BACKGROUND_OPACITY
    ];

    /**
     * @var string Default root directory of the project
     */
    public const DEFAULT_PROJECT_DIR = __DIR__ . '/../..';

    /**
     * @var string Default cache directory
     */
    public const DEFAULT_CACHE_DIR = self::DEFAULT_PROJECT_DIR . '/var/cache';

    /**
     * Root of the project (e.g. '/var/www/jka-server-status')
     */
    public readonly string $projectDir;

    /**
     * @var int $cachingDelay Delay, in seconds, to cache the server-side response
     */
    public readonly int $cachingDelay;

    /**
     * @var int $timeoutDelay Delay, in seconds, to wait for the JKA server response
     */
    public readonly int $timeoutDelay;

    /**
     * @var string $assetUrl Prefix to prepend to asset URLs (no trailing slash)
     */
    public readonly string $assetUrl;

    /**
     * @var bool $isLandingPageEnabled Enable the landing page? (List of JKA servers)
     */
    public readonly bool $isLandingPageEnabled;

    /**
     * @var string $landingPageUri URI of the landing page (e.g. '/')
     */
    public readonly string $landingPageUri;

    /**
     * @var bool $isAboutPageEnabled Is the "About" page enabled?
     */
    public readonly bool $isAboutPageEnabled;

    /**
     * @var string $aboutPageUri URI of the "About" page (e.g. '/about')
     */
    public readonly string $aboutPageUri;

    /**
     * @var string $aboutPageTitle Title of the "About" page, and "About" link (e.g. 'About')
     */
    public readonly string $aboutPageTitle;

    /** @var JkaServerConfigData[] $jkaServers */
    public readonly array $jkaServers;

    /**
     * @var string $cacheDir Directory where the cached HTML versions of status pages are stored
     */
    public readonly string $cacheDir;

    /**
     * @var string|null $canonicalUrl Base URL to use for OpenGraph meta tags (e.g. "og:url") - without trailing slash.
     */
    public readonly ?string $canonicalUrl;

    /**
     * @var int[] $opacityPerMap Opacity percentage (int [0-100]) by image name.
     */
    private readonly array $opacityPerMap;

    /**
     * @param int $cachingDelay Delay, in seconds, to cache the server-side response
     * @param int $timeoutDelay Delay, in seconds, to wait for the JKA server response
     * @param string $assetUrl Prefix to prepend to asset URLs (no trailing slash)
     * @param bool $isLandingPageEnabled Enable the landing page? (List of JKA servers)
     * @param string $landingPageUri URI of the landing page (e.g. '/')
     * @param bool $isAboutPageEnabled Is the "About" page enabled?
     * @param string $aboutPageUri URI of the "About" page (e.g. '/about')
     * @param string $aboutPageTitle Title of the "About" page, and "About" link (e.g. 'About')
     * @param JkaServerConfigData[] $jkaServers
     * @param int[] $opacityPerMap Indexed array. Opacity percentage (int [0-100]) by image name.
     *                             E.g. "mp/siege_korriban" => 30 (= 30% for "mp/siege_korriban.jpg"),
     *                             or "default" => 50 (= 50% for "default.jpg").
     *                             Any map missing from the array will default to ConfigData::DEFAULT_BACKGROUND_OPACITY.
     * @param string $projectDir Root path of the project (filesystem path, not URI)
     * @param string|null $canonicalUrl Base URL to use for OpenGraph meta tags (e.g. "og:url")
     *                                  - without trailing slash.
     */
    public function __construct(
        int $cachingDelay = self::DEFAULT_CACHING_DELAY,
        int $timeoutDelay = self::DEFAULT_TIMEOUT_DELAY,
        string $assetUrl = self::DEFAULT_ASSET_URL,
        bool $isLandingPageEnabled = false,
        string $landingPageUri = self::DEFAULT_LANDING_PAGE_URI,
        bool $isAboutPageEnabled = self::DEFAULT_IS_ABOUT_PAGE_ENABLED,
        string $aboutPageUri = self::DEFAULT_ABOUT_PAGE_URI,
        string $aboutPageTitle = self::DEFAULT_ABOUT_PAGE_TITLE,
        array $jkaServers = [],
        array $opacityPerMap = self::DEFAULT_BACKGROUND_OPACITY_PER_MAP,
        string $projectDir = self::DEFAULT_PROJECT_DIR,
        string $cacheDir = self::DEFAULT_CACHE_DIR,
        ?string $canonicalUrl = null,
    ) {
        $this->cachingDelay = $cachingDelay;
        $this->timeoutDelay = $timeoutDelay;
        $this->assetUrl = $assetUrl;
        $this->isLandingPageEnabled = $isLandingPageEnabled;
        $this->landingPageUri = $landingPageUri;
        $this->isAboutPageEnabled = $isAboutPageEnabled;
        $this->aboutPageUri = $aboutPageUri;
        $this->aboutPageTitle = $aboutPageTitle;
        $this->jkaServers = $jkaServers;
        $this->opacityPerMap = $opacityPerMap;
        $this->projectDir = $projectDir;
        $this->cacheDir = $cacheDir;
        $this->canonicalUrl = $canonicalUrl;
    }

    /**
     * Returns the opacity percentage [0-100], to apply to the specified background image.
     * @param string $mapName The name of the image, without the extension (.jpg), relative to the "levelshots" folder.
     *                        Typically, the map name (e.g. "mp/ffa3") or "default" for "default.jpg".
     * @return int The opacity percentage to apply [0-100]
     */
    public function getBackgroundOpacity(string $mapName): int
    {
        return $this->opacityPerMap[$mapName] ?? self::DEFAULT_BACKGROUND_OPACITY;
    }
}
