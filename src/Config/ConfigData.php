<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

use JkaServerStatus\Config\JkaServerConfigData;

/**
 * General config data. Stores everything except the logging config.
 */
class ConfigData
{
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
    public readonly array $jkaServers; // []

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
     * @param string $projectDir Root path of the project (filesystem path, not URI)
     */
    public function __construct(
        int $cachingDelay,
        int $timeoutDelay,
        string $assetUrl,
        bool $isLandingPageEnabled,
        string $landingPageUri,
        bool $isAboutPageEnabled,
        string $aboutPageUri,
        string $aboutPageTitle,
        array $jkaServers,
        string $projectDir = __DIR__ . '/../..',
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
        $this->projectDir = $projectDir;
    }
}
