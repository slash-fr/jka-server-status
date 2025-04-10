<?php

require_once __DIR__ . '/../src/autoload.php';

use JkaServerStatus\Config\ConfigException;
use JkaServerStatus\Config\ConfigFileException;
use JkaServerStatus\Config\ConfigFileService;
use JkaServerStatus\Config\ConfigService;
use JkaServerStatus\Config\LogConfigException;
use JkaServerStatus\Config\LogConfigService;
use JkaServerStatus\JkaServer\JkaServerService;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Helper\TemplateHelper;
use JkaServerStatus\JkaServer\StatusController;

////////////////////////////////////////////////////////////////////////////////
// Initialize the config

// Check that config.php exists and is readable
try {
    $configFileService = new ConfigFileService();
    $configFile = $configFileService->getConfigFile();
} catch (ConfigFileException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    header('Content-type: text/plain');
    die(
        "JKA Server Status: CONFIGURATION ERROR\n"
        . "\n"
        . "Could not read config.php. Make sure that it exists and is readable by PHP.\n"
    );
}

// Initialize the log config
try {
    $logConfigService = new LogConfigService();
    $logConfig = $logConfigService->getLogConfig($configFile);
} catch (LogConfigException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    header('Content-type: text/plain');
    die(
        "JKA Server Status: CONFIGURATION ERROR\n"
        . "\n"
        . "The specified logging configuration is invalid.\n"
        . "\n"
        . "Please check PHP's system logger instead."
    );
}

// Initialize the logger with the specified settings
$logger = new Logger($logConfig->logFile, $logConfig->logLevel);
// Classes (such as Logger) use dependency injection, by declaring their dependencies in their constructor

// Main config object
try {
    $configService = new ConfigService($logger);
    $config = $configService->getConfig($configFile);
} catch (ConfigException $exception) {
    http_response_code(500);
    header('Content-type: text/plain');
    die(
        "JKA Server Status: CONFIGURATION ERROR.\n"
        . 'Please check the logs.'
    );
}

// Template functions
$templateHelper = new TemplateHelper($config, $logger);
require_once $config->projectDir . '/src/template_functions.php';

////////////////////////////////////////////////////////////////////////////////
// Front controller: Try to match the REQUEST_URI

// URL Path, without the query string (e.g. "/home", not "/home?lang=en")
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Try to match the landing page
if ($config->isLandingPageEnabled && $urlPath === $config->landingPageUri) {
    require_once $config->projectDir . '/templates/landing_page.php';
    exit;
}

// Try to match the "About" page
if ($config->isAboutPageEnabled && $urlPath === $config->aboutPageUri) {
    require_once $config->projectDir . '/templates/about.php';
    exit;
}

// Try to match one of the JKA servers
foreach ($config->jkaServers as $jkaServer) {
    if ($urlPath === $jkaServer->uri) {
        // Output the "status" page (as HTML)
        $jkaServerController = new StatusController(
            new JkaServerService($config, $logger, $templateHelper),
            $config,
            $logger,
            $templateHelper
        );
        echo $jkaServerController->getHtmlStatus($jkaServer); // Handles server-side caching and rendering.
        exit;
    }
}

// Did not match anything => 404 Error
http_response_code(404);
require_once $config->projectDir . '/templates/404.php';
