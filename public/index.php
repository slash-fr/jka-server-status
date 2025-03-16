<?php

require_once __DIR__ . '/../src/autoload.php';

use JkaServerStatus\Config\Config;
use JkaServerStatus\Config\ConfigException;
use JkaServerStatus\Controller\JkaServerController;
use JkaServerStatus\JkaServer\JkaServerService;
use JkaServerStatus\Log\ConfigLogger;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Template\TemplateHelper;

define('PROJECT_DIR', __DIR__ . '/..');
define('DEFAULT_LOG_FILE', PROJECT_DIR . '/var/log/server.log');

// Initialize the config
try {
    // Classes (such as Config) use dependency injection, by declaring their dependencies in their constructor
    $config = new Config(
        PROJECT_DIR . '/config.php',
        // Use a different logger at this stage, because we don't know what log config the user wants, yet.
        new ConfigLogger(DEFAULT_LOG_FILE, LOG_INFO),
        DEFAULT_LOG_FILE,
    );
} catch (ConfigException $exception) {
    http_response_code(500);
    header('Content-type: text/plain');
    die('JKA Server Status: configuration error. Please check the logs.');
}

// Main logger, set to the configured file and level
$logger = new Logger($config->logFile, $config->logLevel);

// Template functions
$templateHelper = new TemplateHelper($config, $logger);
require_once PROJECT_DIR . '/src/template_functions.php';

////////////////////////////////////////////////////////////////////////////////
// Front controller: Try to match the REQUEST_URI

$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($config->isLandingPageEnabled && $urlPath === $config->landingPageUri) {
    require_once PROJECT_DIR . '/templates/landing_page.php';
    exit;
}

foreach ($config->jkaServers as $jkaServer) {
    if ($urlPath === $jkaServer->uri) {
        // Output the "status" page (as HTML)
        $jkaServerController = new JkaServerController(
            new JkaServerService($config, $logger),
            $config,
            $logger,
            $templateHelper
        );
        echo $jkaServerController->getHtmlStatus($jkaServer); // Handles server-side caching and rendering.
        exit;
    }
}

// Did not match the landing page, nor one of the specified JKA servers => 404 Error
http_response_code(404);
require_once PROJECT_DIR . '/templates/404.php';
