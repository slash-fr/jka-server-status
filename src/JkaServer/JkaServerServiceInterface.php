<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

use JkaServerStatus\Config\JkaServerConfigData;

interface JkaServerServiceInterface
{
    /**
     * Returns the status data for the specified JKA server.
     */
    public function getStatusData(JkaServerConfigData $jkaServerConfig): StatusData;
    
    /**
     * Parse the server response, and build the StatusData object
     */
    public function buildStatusData(
        JkaServerConfigData $jkaServerConfig,
        JkaServerResponse $jkaServerResponse,
    ): StatusData;
}
