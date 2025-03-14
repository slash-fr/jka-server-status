<?php

declare(strict_types=1);

class Config
{
    ////////////////////////////////////////////////////////////////////////////
    // Public (read-only) properties

    /**
     * @var string $logFile Path to the log file (e.g. "/var/www/jka-server-status/log/server.log")
     */
    public readonly string $logFile;

    /**
     * @var int $logLevel e.g. LOG_INFO, LOG_WARNING, LOG_ERR
     */
    public readonly int $logLevel;

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

    /** @var JkaServerConfig[] $jkaServers */
    public readonly array $jkaServers; // []

    // End of public properties
    ////////////////////////////////////////////////////////////////////////////

    private readonly ConfigLogger $logger;
    private readonly string $defaultLogFile;
    private readonly int $nbJkaServers;

    /**
     * Initializes the config from the specified file
     * @var string $configFile Path to the PHP config file (e.g. PROJECT_DIR . '/config.php')
     * @var ConfigLogger $configLogger Logger to use if config errors are detected
     * @var string $defaultLogFile Path to the default log file (e.g. "/var/www/jka-server-status/log/server.log")
     * @throws ConfigException if the config is invalid
     */
    public function __construct(string $configFile, Logger $configLogger, string $defaultLogFile)
    {
        $this->logger = $configLogger;
        $this->defaultLogFile = $defaultLogFile;

        if (!file_exists($configFile)) {
            $message = 'Could not find the configuration file ("' . $configFile . '").';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        if (!is_readable($configFile)) {
            $message = 'Could not read the configuration file ("' . $configFile . '"). '
                . 'Please check file permissions.';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        @include $configFile;

        $this->initLogConfig($log_file ?? null, $log_level ?? null);
        $this->initCachingDelay($caching_delay ?? null);
        $this->initTimeoutDelay($timeout_delay ?? null);
        $this->initRootUrl($root_url ?? null);
        $this->initJkaServers($jka_servers ?? null);
        $this->initIsLandingPageEnabled($enable_landing_page ?? null);
        $this->initLandingPageUri($landing_page_uri ?? null);
        $this->validateUniqueURIs();
    }

    private function initLogConfig(mixed $logFile, mixed $logLevel): void
    {
        // Log file
        if (!isset($logFile)) {
            $logFile = $this->defaultLogFile;
        }

        if (!is_string($logFile)) {
            $message = 'Config variable $log_file must be a string (got: ' . gettype($logFile) . ')';
            error_log(date('Y-m-d H:i:s') . ' - ERROR - ' . $message);
            throw new ConfigException($message);
        }

        $this->logFile = $logFile;

        // Now that we know what log file the user wants, let's log to that file.
        $this->logger->setLogFile($this->logFile);

        // Level
        if (!isset($logLevel)) {
            $logLevel = LOG_INFO;
        }

        if (!is_int($logLevel)) {
            $logLevel = LOG_INFO;
            $this->logger->warning(
                'Config variable $log_level must be an int (got: ' . gettype($logLevel) . '). Defaulting to LOG_INFO. '
                . 'Tip: use one of the constants (LOG_INFO, LOG_WARNING, LOG_ERR), or 0 to disable logging.'
            );
        }

        $this->logLevel = $logLevel;

        // Now that we know level the user wants, let's use it.
        $this->logger->setLevel(max(LOG_ERR, $this->logLevel)); // Always log config errors (at least)

        // If logging is enabled, verify that the log file is writable.
        if ($logLevel > 0) {
            $handle = @fopen($this->logFile, 'a'); // Append mode
            if ($handle === false) {
                $message = 'Could not open the application-level log file for writing ("' . $this->logFile . '").';
                error_log(date('Y-m-d H:i:s') . ' - ERROR - ' . $message);
                // Don't throw an exception here.
                // It would be OK during the initial setup, but not for subsequent runs
                // (it would break every page if the log file somehow becomes unwritable).
            }
            fclose($handle);
        }
    }

    private function initCachingDelay(mixed $cachingDelay): void
    {
        if (!isset($cachingDelay)) {
            $this->cachingDelay = 10; // 10 seconds
            return;
        }

        if (!is_int($cachingDelay)) {
            $this->logger->warning(
                'Config variable $caching_delay must be an int (got: ' . gettype($cachingDelay) . ').'
            );
        }

        $this->cachingDelay = (int)$cachingDelay;
    }

    private function initTimeoutDelay(mixed $timeoutDelay): void
    {
        if (!isset($timeoutDelay)) {
            $this->timeoutDelay = 3; // 3 seconds
            return;
        }

        if (!is_int($timeoutDelay)) {
            $this->logger->warning(
                'Config variable $timeout_delay must be an int (got: ' . gettype($timeoutDelay) . ').
            ');
        }

        $this->timeoutDelay = (int)$timeoutDelay;

        if ($this->timeoutDelay < 1) {
            $this->timeoutDelay = 1;
            $this->logger->warning('Config variable $timeout_delay must be >= 1. Changing to 1.');
        }
    }

    private function initRootUrl(mixed $rootUrl): void
    {
        if (!isset($rootUrl)) {
            $this->rootUrl = '';
            return;
        }

        if (!is_string($rootUrl)) {
            $this->logger->warning('Config variable $root_url must be a string (got: ' . gettype($rootUrl) . ').');
        }

        $this->rootUrl = rtrim((string)$rootUrl, '/'); // Remove the trailing slash
    }

    private function initJkaServers(mixed $jkaServers): void
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

        $this->nbJkaServers = count($jkaServers);

        if ($this->nbJkaServers < 1) {
            $message = 'Config variable $jka_servers must contain at least 1 server.';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $jkaServerObjects = [];

        foreach ($jkaServers as $index => $jkaServer) {
            $jkaServerObjects[] = $this->buildOneJkaServer($index, $jkaServer);
        }

        $this->jkaServers = $jkaServerObjects;
    }

    private function buildOneJkaServer(string|int $index, mixed $jkaServer): JkaServerConfig
    {
        if (!is_array($jkaServer)) {
            $message = 'Config variable $jka_servers must be an array of arrays '
                . '(got: ' . gettype($jkaServer) . ' for $jka_servers[' . var_export($index, true) . '])';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $jkaServerUri = $this->sanitizeServerUri($index, $jkaServer);
        $jkaServerAddress = $this->sanitizeServerAddress($index, $jkaServer);
        $jkaServerName = $this->sanitizeServerName($index, $jkaServer, $jkaServerAddress);
        $jkaServerCharset = $this->sanitizeServerCharset($index, $jkaServer);

        return new JkaServerConfig($jkaServerUri, $jkaServerAddress, $jkaServerName, $jkaServerCharset);
    }

    private function sanitizeServerUri(string|int $index, array $jkaServer): string
    {
        if (!isset($jkaServer['uri']) && $this->nbJkaServers > 1) {
            $message = 'A "uri" field is required for each server (when multiple servers are configured). '
                . ' $jka_servers[' . $index . '] does not specify a "uri".';
            $this->logger->error($message);
            throw new ConfigException($message);
        }
        $uri = '/';
        if (isset($jkaServer['uri'])) {
            $uri = $jkaServer['uri'];
        }

        if (!is_string($uri)) {
            $message = 'The "uri" of each configured server must be a string '
                . '(got: ' . gettype($uri) . ' for $jka_servers[' . $index . ']["uri"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $uri;
    }

    private function sanitizeServerAddress(string|int $index, array $jkaServer): string
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
                . '(got: ' . gettype($address) . ' for $jka_servers[' . $index . ']["address"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        $fullUrl = JkaServerService::buildFullUdpUrl($address);
        if (!filter_var($fullUrl, FILTER_VALIDATE_URL)) {
            $message = 'Invalid JKA server address: "' . $address . '" for $jka_servers[' . $index . '].';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $address;
    }

    /**
     * @param string $jkaServerAddress The sanitized server address (used as default value if the name is missing)
     */
    private function sanitizeServerName(string|int $index, array $jkaServer, string $jkaServerAddress): string
    {
        if (!isset($jkaServer['name'])) {
            return $jkaServerAddress;
        }

        if (!is_string($jkaServer['name'])) {
            $message = 'The "name" of each configured server must be a string '
                . '(got: ' . gettype($jkaServer['name']) . ' for $jka_servers[' . $index . ']["name"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $jkaServer['name'];
    }

    private function sanitizeServerCharset(string|int $index, array $jkaServer): string
    {
        $charset = 'Windows-1252';

        if (isset($jkaServer['charset'])) {
            $charset = $jkaServer['charset'];
        }

        if (!is_string($charset)) {
            $message = 'The "charset" of each configured server must be a string '
                . '(got: ' . gettype($jkaServer['charset']) . ' for $jka_servers[' . $index . ']["charset"]).';
            $this->logger->error($message);
            throw new ConfigException($message);
        }
        
        // Check whether the specified charset works
        $encodingTest = Charset::toUtf8('123456', $charset);
        if (!$encodingTest) {
            $message = 'Unsupported "charset" ("' . $charset . '") for $jka_servers[' . $index . ']["charset"]';
            $this->logger->error($message);
            throw new ConfigException($message);
        }

        return $charset;
    }

    /**
     * Must be called AFTER initJkaServers()
     */
    private function initIsLandingPageEnabled(mixed $isLandingPageEnabled): void
    {
        if (!isset($isLandingPageEnabled)) {
            // By default, enable the landing page only when multiple JKA servers are declared
            $this->isLandingPageEnabled = ($this->nbJkaServers > 1);
            return;
        }

        if (!is_bool($isLandingPageEnabled)) {
            $this->logger->warning(
                'Config variable $enable_landing_page must be a boolean (got: ' . gettype($isLandingPageEnabled) . ').'
            );
        }

        $this->isLandingPageEnabled = (bool)$isLandingPageEnabled;
    }

    private function initLandingPageUri(mixed $landingPageUri): void
    {
        if (!isset($landingPageUri)) {
            $this->landingPageUri = '/';
            return;
        }
        
        if (!is_string($landingPageUri)) {
            $this->logger->warning(
                'Config variable $landing_page_uri must be a string (got: ' . gettype($landingPageUri) . ').'
            );
        }

        $this->landingPageUri = (string)$landingPageUri;
    }

    private function validateUniqueURIs(): void
    {
        $uris = []; // Indexed array, e.g. ["/uri" => "label"]

        if ($this->isLandingPageEnabled) {
            $uris[$this->landingPageUri] = 'the landing page URI';
        }

        foreach ($this->jkaServers as $index => $jkaServer) {
            if (isset($uris[$jkaServer->uri])) {
                $message = '$jka_servers[' . $index . ']["uri"] conflicts with ' . $uris[$jkaServer->uri] . '.';
                $this->logger->error($message);
                throw new ConfigException($message);
            }
            $uris[$jkaServer->uri] = '$jka_servers[' . $index . ']["uri"]';
        }
    }
}
