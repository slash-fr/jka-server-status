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
     * @var string $rootUrl Prefix to prepend to URLs
     */
    public readonly string $rootUrl;

    /**
     * @var bool $isLandingPageEnabled Enable the landing page? (List of JKA servers)
     */
    public readonly bool $isLandingPageEnabled;

    /**
     * @var string $landingPageUri URI of the landing page (e.g. '/')
     */
    public readonly string $landingPageUri;

    /** @var JkaServerConfigData[] $jkaServers */
    public readonly array $jkaServers; // []

    /**
     * @param int $cachingDelay Delay, in seconds, to cache the server-side response
     * @param int $timeoutDelay Delay, in seconds, to wait for the JKA server response
     * @param string $rootUrl Prefix to prepend to URLs (no trailing slash)
     * @param bool $isLandingPageEnabled Enable the landing page? (List of JKA servers)
     * @param string $landingPageUri URI of the landing page (e.g. '/')
     * @param JkaServerConfigData[] $jkaServers
     */
    public function __construct(
        int $cachingDelay,
        int $timeoutDelay,
        string $rootUrl,
        bool $isLandingPageEnabled,
        string $landingPageUri,
        array $jkaServers,
        $projectDir = __DIR__ . '/../..',
    ) {
        $this->cachingDelay = $cachingDelay;
        $this->timeoutDelay = $timeoutDelay;
        $this->rootUrl = $rootUrl;
        $this->isLandingPageEnabled = $isLandingPageEnabled;
        $this->landingPageUri = $landingPageUri;
        $this->jkaServers = $jkaServers;
        $this->projectDir = $projectDir;
    }
}
