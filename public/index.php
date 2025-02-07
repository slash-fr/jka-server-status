<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/functions.php';

if ($enable_landing_page && $_SERVER['REQUEST_URI'] === $landing_page_uri) {
    require_once __DIR__ . '/../src/landing-page.php';
    exit;
}

foreach ($jka_servers as $jka_server) {
    if ($_SERVER['REQUEST_URI'] === $jka_server['uri']) {
        print_server_status($jka_server['address'], $jka_server['name'], $jka_server['charset']);
        exit;
    }
}

// Did not match the landing page, nor one of the specified JKA servers => 404 Error
http_response_code(404);
require_once __DIR__ . '/../src/404.php';
