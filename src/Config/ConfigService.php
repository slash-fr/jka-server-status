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
        $this->validateUniqueURIs($jkaServers, $isLandingPageEnabled, $landingPageUri);

        return new ConfigData(
            $cachingDelay,
            $timeoutDelay,
            $rootUrl,
            $isLandingPageEnabled,
            $landingPageUri,
            $jkaServers
        );
    }

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
     */
    private function sanitizeServerSubtitle(array $jkaServer, string|int $index): string
    {
        if (!isset($jkaServer['subtitle'])) {
            return '';
        }

        if (!is_string($jkaServer['subtitle'])) {
            $message = 'The "subtitle" of each configured server must be a string (got: ' . gettype($jkaServer['subtitle'])
                . ' for $jka_servers[' . var_export($index, true) . ']["subtitle"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return trim($jkaServer['subtitle']);
    }

    /**
     * @param array $jkaServer ONE entry from the $jka_servers config variable (should be an indexed array)
     * @param string|int $index Index of the server within $jka_servers (should be an int)
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
     * @param JkaServerConfigData[] $jka_servers
     */
    private function validateUniqueURIs(array $jkaServers, bool $isLandingPageEnabled, string $landingPageUri): void
    {
        $uris = []; // Indexed array, e.g. ["/uri" => "label"]

        if ($isLandingPageEnabled) {
            $uris[$landingPageUri] = 'the landing page URI';
        }

        foreach ($jkaServers as $index => $jkaServer) {
            if (isset($uris[$jkaServer->uri])) {
                $message = '$jka_servers[' . var_export($index, true) . ']["uri"] '
                    . 'conflicts with ' . $uris[$jkaServer->uri] . '.';
                $this->logger->error($message);
                throw new ConfigException($message);
            }
            $uris[$jkaServer->uri] = '$jka_servers[' . $index . ']["uri"]';
        }
    }
}
