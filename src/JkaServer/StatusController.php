<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Config\JkaServerConfigData;
use JkaServerStatus\JkaServer\JkaServerServiceInterface;
use JkaServerStatus\JkaServer\StatusData;
use JkaServerStatus\Log\LoggerInterface;
use JkaServerStatus\Helper\TemplateHelper;
use RuntimeException;

/**
 * Controller that handles the "status" page
 */
class StatusController
{
    private readonly JkaServerServiceInterface $jkaServerService;
    private readonly ConfigData $config;
    private readonly LoggerInterface $logger;
    private readonly TemplateHelper $templateHelper;

    public function __construct(
        JkaServerServiceInterface $jkaServerService,
        ConfigData $config,
        LoggerInterface $logger,
        TemplateHelper $templateHelper
    ) {
        $this->jkaServerService = $jkaServerService;
        $this->config = $config;
        $this->logger = $logger;
        $this->templateHelper = $templateHelper;
    }

    /**
     * Returns the "status" page (as HTML). Handles server-side caching and rendering.
     */
    public function getHtmlStatus(JkaServerConfigData $jkaServerConfig): string
    {
        // Return the cached version, if available (and still fresh)
        $cachedHtml = $this->getCachedHtml($jkaServerConfig->address);
        if ($cachedHtml) {
            return $cachedHtml;
        }
        
        // Otherwise, query the JKA server and parse its response
        $data = $this->jkaServerService->getStatusData($jkaServerConfig);

        return $this->renderAndCacheHtml($jkaServerConfig->address, $data);
    }

    /**
     * Returns the cached version, if available (and still fresh), for the specified JKA Server address.
     * @param string $jkaServerAddress IP address or domain name of the JKA Server, with optional port
     *                                 (e.g. "192.0.2.1")
     * 
     * @return string|false The cached HTML content, if available (and still fresh), false otherwise.
     */
    private function getCachedHtml(string $jkaServerAddress): string|false
    {
        $cachedFilename = $this->getCachedFilename($jkaServerAddress);
        if (!file_exists($cachedFilename)) {
            // The file hasn't been created yet
            // Not a problem (it will be created later)
            return false;
        }

        if (!is_file($cachedFilename)) {
            // The "file" exists but is a directory (or a link)
            $this->logger->error(
                $jkaServerAddress . ' - The cached version is not a regular file ("' . $cachedFilename . '").'
            );
            return false;
        }

        if (!is_readable($cachedFilename)) {
            $this->logger->error(
                $jkaServerAddress . ' - The cached version is not readable ("' . $cachedFilename . '").'
            );
            return false;
        }

        $cachedAt = filemtime($cachedFilename);
        if ($cachedAt === false) {
            $this->logger->error(
                $jkaServerAddress . '- Could not determine the modification time of the cached version '
                . '("' . $cachedFilename . '").'
            );
            return false;
        }

        if (time() >= $cachedAt + $this->config->cachingDelay) {
            // The cached version is outdated
            // Not a problem (it will get refreshed)
            return false;
        }

        // The cached version is still fresh
        $this->logger->info($jkaServerAddress . ' - from cache');
        $htmlStatus = file_get_contents($cachedFilename);
        if (!$htmlStatus) {
            // file_get_contents() didn't work
            $this->logger->error(
                $jkaServerAddress . ' - could not read the cached version ("' . $cachedFilename . '").'
            );
            return false;
        }

        return $htmlStatus; // Return the cached HTML
    }

    /**
     * Renders the HTML and caches it.
     * @param string $jkaServerAddress IP address or domain name of the JKA Server, with optional port
     *                                 (e.g. "192.0.2.1")
     * @param StatusData $statusData The status data necessary to render the page
     * 
     * @return string The HTML version of the status page.
     */
    private function renderAndCacheHtml(string $jkaServerAddress, StatusData $data): string
    {
        $this->logger->info(
            $jkaServerAddress . ' - Generating HTML'
            . ' - Status: ' . $data->status
            . ' - Name: "' . $this->templateHelper->stripColors($data->serverName) . '"'
            . ' - Map: "' . ($data->mapName ?? '?') . '"'
            . ' - Game type: ' . ($data->cvars['g_gametype'] ?? '?') . ' (' . ($data->gameType ?? '?') . ')'
            . ' - Players: ' . ($data->nbPlayers ?? '?') . ' / ' . ($data->maxPlayers ?? '?')
            . ' - ' . ($data->nbHumans ?? '?') . ' human(s) + ' . ($data->nbBots ?? '?') . ' bot(s)'
        );

        // Enable output buffering
        if (!ob_start()) {
            $message = 'ob_start() failed!';
            $this->logger->error($message);
            throw new RuntimeException($message);
        }

        // "status.php" needs the $data variable
        require_once $this->config->projectDir . '/templates/status.php';

        // Get the content of the buffer
        $htmlStatus = ob_get_clean();
        if ($htmlStatus === false) {
            $message = 'ob_get_clean() failed!';
            $this->logger->error($message);
            throw new RuntimeException($message);
        }

        if ($this->config->cachingDelay > 0) {
            $cachedFilename = $this->getCachedFilename($jkaServerAddress);
            if (!file_put_contents($cachedFilename, $htmlStatus)) {
                $this->logger->error($jkaServerAddress . ' - could not cache the HTML ("' . $cachedFilename . '").');
            }
        }
        
        return $htmlStatus;
    }

    /**
     * Returns the path to the cached version, for the specified JKA Server address
     * @param string $jkaServerAddress IP address or domain name of the JKA Server, with optional port
     *                                 (e.g. "192.0.2.1")
     * 
     * @return string Path to the cached version (e.g. "/var/www/jka-server-status/var/cache/192-0-2-1.html")
     */
    private function getCachedFilename(string $jkaServerAddress): string
    {
        // Sanitize the host for use as a filename:
        return $this->config->projectDir . '/var/cache/'
            . preg_replace('/[^a-z0-9]/', '-', strtolower($jkaServerAddress)) . '.html';
    }
}
