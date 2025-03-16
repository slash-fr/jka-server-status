<?php declare(strict_types=1);

namespace JkaServerStatus\Controller;

use JkaServerStatus\Config\Config;
use JkaServerStatus\Config\JkaServerConfig;
use JkaServerStatus\JkaServer\JkaServerService;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Template\TemplateHelper;

/**
 * Controller that handles the "status" page
 */
class JkaServerController
{
    private readonly JkaServerService $jkaServerService;
    private readonly Config $config;
    private readonly Logger $logger;
    private readonly TemplateHelper $templateHelper;

    public function __construct(
        JkaServerService $jkaServerService,
        Config $config,
        Logger $logger,
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
    public function getHtmlStatus(JkaServerConfig $jkaServerConfig): string
    {
        // Sanitize the host for use as a filename:
        $cachedFile = PROJECT_DIR . '/var/cache/' . preg_replace('/[^a-z0-9]/', '-', strtolower($jkaServerConfig->address)) . '.html';
        $cachingDelay = $this->config->cachingDelay;
        // Try to get the HTML from the cache
        $cachedAt = @filemtime($cachedFile);
        if (time() < $cachedAt + $cachingDelay) { // Shorter than the configured delay (in seconds)
            $this->logger->info($jkaServerConfig->address . ' - from cache');
            $htmlStatus = file_get_contents($cachedFile);
            if ($htmlStatus) {
                return $htmlStatus; // Return the cached HTML
            }
            // file_get_contents() didn't work
            $this->logger->warning($jkaServerConfig->address . ' - could not read the cached HTML');
        }
        
        // Query the JKA server and parse its response
        $data = $this->jkaServerService->getStatusData($jkaServerConfig);

        if (!$data->isUp) {
            $this->logger->error($jkaServerConfig->address . ' - Status: ' . $data->status);
        }

        $this->logger->info(
            $jkaServerConfig->address . ' - Generating HTML'
            . ' - Status: ' . $data->status
            . ' - Name: "' . $this->templateHelper->stripColors($data->serverName) . '"'
            . ' - Map: "' . ($data->mapName ?? '?') . '"'
            . ' - Game type: ' . ($data->cvars['g_gametype'] ?? '?') . ' (' . ($data->gameType ?? '?') . ')'
            . ' - Players: ' . ($data->nbPlayers ?? '?') . ' / ' . ($data->maxPlayers ?? '?')
            . ' - ' . ($data->nbHumans ?? '?') . ' human(s) + ' . ($data->nbBots ?? '?') . ' bot(s)'
        );

        // Render and cache the HTML
        ob_start();
        require_once PROJECT_DIR . '/templates/status.php';
        $htmlStatus = ob_get_clean();
        if ($cachingDelay > 0) {
            if (!file_put_contents($cachedFile, $htmlStatus)) {
                $this->logger->info($jkaServerConfig->address . ' - could not cache the HTML');
            }
        }

        return $htmlStatus;
    }
}
