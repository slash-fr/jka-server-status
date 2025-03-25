<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

use JkaServerStatus\JkaServer\JkaServerService;
use JkaServerStatus\Log\LoggerInterface;
use JkaServerStatus\Util\Charset;

class ConfigService
{
    private readonly LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger Logger to use if config errors are detected
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Initializes the config from the specified configuration file.
     * @param string $pathToconfigFile Path to the PHP config file (e.g. __DIR__ . '/../config.php')
     *                                 It MUST exist and be readable.
     * 
     * @return ConfigData
     * @throws ConfigException if the config is invalid
     */
    public function getConfig(string $pathToConfigFile): ConfigData
    {
        require $pathToConfigFile;

        $cachingDelay = $this->sanitizeCachingDelay($caching_delay ?? null);
        $timeoutDelay = $this->sanitizeTimeoutDelay($timeout_delay ?? null);
        $rootUrl = $this->sanitizeRootUrl($root_url ?? null);
        $jkaServers = $this->getJkaServers($jka_servers ?? null);
        $isLandingPageEnabled = $this->isLandingPageEnabled($enable_landing_page ?? null, count($jkaServers));
        $landingPageUri = $this->sanitizeLandingPageUri($landing_page_uri ?? null);
        $isAboutPageEnabled = $this->sanitizeIsAboutPageEnabled($enable_about_page ?? null);
        $aboutPageUri = $this->sanitizeAboutPageUri($about_page_uri ?? null);
        $aboutPageTitle = $this->sanitizeAboutPageTitle($about_page_title ?? null);
        $this->validateUniqueURIs(
            $jkaServers,
            $isLandingPageEnabled ? $landingPageUri : null,
            $isAboutPageEnabled ? $aboutPageUri : null,
        );

        return new ConfigData(
            $cachingDelay,
            $timeoutDelay,
            $rootUrl,
            $isLandingPageEnabled,
            $landingPageUri,
            $isAboutPageEnabled,
            $aboutPageUri,
            $aboutPageTitle,
            $jkaServers,
            __DIR__ . '/../..',
        );
    }

    /**
     * @param mixed $cachingDelay Config variable $caching_delay from "config.php".
     *                            Should be an int (or null if not set).
     * 
     * @return int The sanitized caching delay.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeCachingDelay(mixed $cachingDelay): int
    {
        if (!isset($cachingDelay)) {
            return 10; // Default value: 10 seconds
        }

        if (!is_int($cachingDelay)) {
            $message = 'Config variable $caching_delay must be an int (got: ' . gettype($cachingDelay) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $cachingDelay;
    }

    /**
     * @param mixed $timeoutDelay Config variable $timeout_delay from "config.php".
     *                            Should be an int (or null if not set).
     * 
     * @return int The sanitized timeout delay.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeTimeoutDelay(mixed $timeoutDelay): int
    {
        if (!isset($timeoutDelay)) {
            return 3; // Default value: 3 seconds
        }

        if (!is_int($timeoutDelay)) {
            $message = 'Config variable $timeout_delay must be an int (got: ' . gettype($timeoutDelay) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        if ($timeoutDelay < 1) {
            $message = 'Config variable $timeout_delay must be >= 1.';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $timeoutDelay;
    }

    /**
     * @param mixed $rootUrl Config variable $root_url from "config.php". Should be a string (or null if not set).
     * 
     * @return string The sanitized root URL.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeRootUrl(mixed $rootUrl): string
    {
        if (!isset($rootUrl)) {
            return '';
        }

        if (!is_string($rootUrl)) {
            $message = 'Config variable $root_url must be a string (got: ' . gettype($rootUrl) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return rtrim((string)$rootUrl, '/'); // Remove the trailing slash
    }

    /**
     * @param mixed $jkaServers Config variable $jka_servers from "config.php" (should be an array of arrays).
     * 
     * @return JkaServerConfigData[]
     * @throws ConfigException if the input value is invalid.
     */
    private function getJkaServers(mixed $jkaServers): array
    {
        if (!isset($jkaServers)) {
            $message = 'Config variable $jka_servers is required.';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        if (!is_array($jkaServers)) {
            $message = 'Config variable $jka_servers must be an array (got: ' . gettype($jkaServers) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $nbJkaServers = count($jkaServers);

        if ($nbJkaServers < 1) {
            $message = 'Config variable $jka_servers must contain at least 1 server.';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $jkaServerConfigs = [];

        foreach ($jkaServers as $index => $jkaServer) {
            $jkaServerConfigs[] = $this->buildOneJkaServer($jkaServer, $index, $nbJkaServers);
        }

        return $jkaServerConfigs;
    }

    /**
     * @param mixed $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
     * 
     * @return JkaServerConfigData
     * @throws ConfigException if the input value is invalid.
     */
    private function buildOneJkaServer(mixed $jkaServer, string|int $index, int $totalNbServers): JkaServerConfigData
    {
        if (!is_array($jkaServer)) {
            $message = 'Config variable $jka_servers must be an array of arrays '
                . '(got: ' . gettype($jkaServer) . ' for $jka_servers[' . var_export($index, true) . '])';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $jkaServerUri = $this->sanitizeServerUri($jkaServer, $index, $totalNbServers);
        $jkaServerAddress = $this->sanitizeServerAddress($jkaServer, $index);
        $jkaServerName = $this->sanitizeServerName($jkaServer, $index, $jkaServerAddress);
        $jkaServerSubtitle = $this->sanitizeServerSubtitle($jkaServer, $index);
        $jkaServerCharset = $this->sanitizeServerCharset($jkaServer, $index);

        return new JkaServerConfigData(
            $jkaServerUri,
            $jkaServerAddress,
            $jkaServerName,
            $jkaServerSubtitle,
            $jkaServerCharset
        );
    }

    /**
     * @param mixed $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
     * 
     * @return string The sanitized URI.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeServerUri(mixed $jkaServer, string|int $index, int $totalNbServers): string
    {
        if (!isset($jkaServer['uri']) && $totalNbServers > 1) {
            $message = 'A "uri" field is required for each server (when multiple servers are configured). '
                . ' $jka_servers[' . var_export($index, true) . '] does not specify a "uri".';
            $this->logger->error($message);
            throw new ConfigException($message);
        }
        $uri = '/';
        if (isset($jkaServer['uri'])) {
            $uri = $jkaServer['uri'];
        }

        if (!is_string($uri)) {
            $message = 'The "uri" of each configured server must be a string '
                . '(got: ' . gettype($uri) . ' for $jka_servers[' . var_export($index, true) . ']["uri"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $uri;
    }

    /**
     * @param array $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
     * 
     * @return string The sanitized server address.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeServerAddress(array $jkaServer, string|int $index): string
    {
        if (!isset($jkaServer['address'])) {
            $message = 'Each configured server must specify an "address". '
                . '$jka_servers[' . $index . '] does not specify an "address".';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $address = $jkaServer['address'];
        if (!is_string($address)) {
            $message = 'The "address" of each configured server must be a string '
                . '(got: ' . gettype($address) . ' for $jka_servers[' . var_export($index, true) . ']["address"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $invalidAddressMessage = 'Invalid JKA server address: "' . $address . '" '
            . 'for $jka_servers[' . var_export($index, true) . '].';

        $fullUdpUrl = JkaServerService::buildFullUdpUrl($address);
        if (!filter_var($fullUdpUrl, FILTER_VALIDATE_URL)) {
            $this->logger->error($invalidAddressMessage);
            throw new ConfigException($invalidAddressMessage);
        }

        if (!preg_match('/^udp:\/\/(.*):[0-9]{1,5}$/', $fullUdpUrl, $matches)) {
            $this->logger->error($invalidAddressMessage);
            throw new ConfigException($invalidAddressMessage);
        }

        $ipOrDomain = $matches[1];
        if (
            !filter_var($ipOrDomain, FILTER_VALIDATE_IP)
            && !filter_var($ipOrDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
        ) {
            $this->logger->error($invalidAddressMessage);
            throw new ConfigException($invalidAddressMessage);
        }

        return $address;
    }

    /**
     * @param array $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
     * @param string $jkaServerAddress The sanitized server address (used as default value if the name is missing)
     * 
     * @return string The sanitized server name.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeServerName(array $jkaServer, string|int $index, string $jkaServerAddress): string
    {
        if (!isset($jkaServer['name'])) {
            return $jkaServerAddress;
        }

        if (!is_string($jkaServer['name'])) {
            $message = 'The "name" of each configured server must be a string (got: ' . gettype($jkaServer['name'])
                . ' for $jka_servers[' . var_export($index, true) . ']["name"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $jkaServer['name'];
    }

    /**
     * @param array $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
     * 
     * @return string The sanitized subtitle.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeServerSubtitle(array $jkaServer, string|int $index): string
    {
        if (!isset($jkaServer['subtitle'])) {
            return '';
        }

        if (!is_string($jkaServer['subtitle'])) {
            $message = 'The "subtitle" of each configured server must be a string (got: '
                . gettype($jkaServer['subtitle']) . ' for $jka_servers[' . var_export($index, true) . ']["subtitle"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return trim($jkaServer['subtitle']);
    }

    /**
     * @param array $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
     * 
     * @return string The sanitized charset name.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeServerCharset(array $jkaServer, string|int $index): string
    {
        $charset = 'Windows-1252';

        if (isset($jkaServer['charset'])) {
            $charset = $jkaServer['charset'];
        }

        if (!is_string($charset)) {
            $message = 'The "charset" of each configured server must be a string '
                . '(got: ' . gettype($jkaServer['charset'])
                . ' for $jka_servers[' . var_export($index, true) . ']["charset"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }
        
        // Check whether the specified charset works
        $encodingTest = Charset::toUtf8('123456', $charset);
        if (!$encodingTest) {
            $message = 'Unsupported "charset" ("' . $charset . '") '
                . 'for $jka_servers[' . var_export($index, true) . ']["charset"]';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $charset;
    }

    /**
     * @param mixed $isLandingPageEnabled Config variable $enable_landing_page from "config.php".
     *                                    Should be a bool (or null if not set).
     * @param int $nbJkServers Number of configured JKA Servers (use getJkaServers() before isLandingPageEnabled()).
     * 
     * @return bool The sanitized value.
     * @throws ConfigException if the input value is invalid.
     */
    private function isLandingPageEnabled(mixed $isLandingPageEnabled, int $nbJkaServers): bool
    {
        if (!isset($isLandingPageEnabled)) {
            // By default, enable the landing page only when multiple JKA servers are declared
            return ($nbJkaServers > 1);
        }

        if (!is_bool($isLandingPageEnabled)) {
            $message = 'Config variable $enable_landing_page must be a boolean '
                . '(got: ' . gettype($isLandingPageEnabled) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $isLandingPageEnabled;
    }

    /**
     * @param mixed $landingPageUri Config variable $landing_page_uri from "config.php".
     *                              Should be a string (or null if not set).
     * 
     * @return string The sanitized landing page URI.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeLandingPageUri(mixed $landingPageUri): string
    {
        if (!isset($landingPageUri)) {
            return '/'; // Default value
        }
        
        if (!is_string($landingPageUri)) {
            $message = 'Config variable $landing_page_uri must be a string (got: ' . gettype($landingPageUri) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $landingPageUri;
    }

    /**
     * @param mixed $isAboutPageEnabled Config value $enable_about_page from "config.php".
     *                                  Should be a boolean (or null if not set).
     * 
     * @return bool The sanitized value.
     * @throws ConfigException if the input value is invalid.
     */
    private function sanitizeIsAboutPageEnabled(mixed $isAboutPageEnabled): bool
    {
        if (!isset($isAboutPageEnabled)) {
            return false; // Disabled by default
        }

        if (!is_bool($isAboutPageEnabled)) {
            $message = 'Config variable $enable_about_page must be a boolean '
                . '(got: ' . gettype($isAboutPageEnabled) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $isAboutPageEnabled;
    }

    /**
     * @param mixed $aboutPageUri Config value $about_page_uri from "config.php".
     *                            Should be a string (or null if not set).
     * 
     * @return string The sanitized URI.
     * @throws ConfigException if the input value is invalid.
     */
    public function sanitizeAboutPageUri(mixed $aboutPageUri): string
    {
        if (!isset($aboutPageUri)) {
            return '/about';
        }

        if (!is_string($aboutPageUri)) {
            $message = 'Config variable $about_page_uri must be a string '
                . '(got: ' . gettype($aboutPageUri) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $aboutPageUri;
    }

    /**
     * @param mixed $aboutPageTitle Config value $about_page_title from "config.php".
     *                              Should be a string (or null if not set).
     * 
     * @return string The sanitized title.
     * @throws ConfigException if the input value is invalid.
     */
    public function sanitizeAboutPageTitle(mixed $aboutPageTitle): string
    {
        if (!isset($aboutPageTitle)) {
            return 'About';
        }

        if (!is_string($aboutPageTitle)) {
            $message = 'Config variable $about_page_title must be a string '
                . '(got: ' . gettype($aboutPageTitle) . ').';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $aboutPageTitle;
    }

    /**
     * @param JkaServerConfigData[] $jka_servers
     * @param string|null $landingPageUri URI of the landing page, if enabled (null otherwise).
     * @param string|null $aboutPageUri URI of the "About" page, if enabled (null otherwise).
     * 
     * @throws ConfigException if there's a conflict between some URIs.
     */
    private function validateUniqueURIs(array $jkaServers, ?string $landingPageUri, ?string $aboutPageUri): void
    {
        $uris = []; // Indexed array, e.g. ["/uri" => "the page it corresponds to"]

        if (isset($landingPageUri)) {
            $uris[$landingPageUri] = 'the landing page URI';
        }

        if (isset($aboutPageUri)) {
            if (isset($uris[$aboutPageUri])) {
                // Conflicts with the landing page URI
                $message = 'Config variable $about_page_uri conflicts with ' . $uris[$aboutPageUri] . '.';
                $this->logger->error($message);
                throw new ConfigException($message);
            }
            $uris[$aboutPageUri] = 'the "About" page URI';
        }

        foreach ($jkaServers as $index => $jkaServer) {
            if (isset($uris[$jkaServer->uri])) {
                // Conflicts with: the landing page URL, the "About" page URI, or another server
                $message = '$jka_servers[' . var_export($index, true) . ']["uri"] '
                    . 'conflicts with ' . $uris[$jkaServer->uri] . '.';
                $this->logger->error($message);
                throw new ConfigException($message);
            }
            $uris[$jkaServer->uri] = '$jka_servers[' . $index . ']["uri"]';
        }
    }
}
