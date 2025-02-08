<?php

// Application-level logging configuration
$log_level = 'INFO';
// false => no logging
// 'INFO' => logs 'INFO' messages and higher levels ('ERROR')
// 'ERROR' => logs 'ERROR' messages only
// Application-level messages will be stored in `log/server.log`
// => Make sure the file permissions allow PHP to write into that file.

// Root URL (trailing slash required)
// e.g. "/server-status/" if you're hosting the script in a subfolder of your actual web root
define('ROOT_URL', '/');

// Enable the landing page? (List of JKA servers)
$enable_landing_page = true; // If you have only 1 server, you should probably set it to `false`
$landing_page_uri = '/';

$jka_servers = [
    // First JKA server
    [
        // URI for the status page
        'uri' => '/main-server',
        // If you have only 1 server, and you've disabled the landing page, you could use just '/'

        // IP address or domain name of the JKA server, with optional port (defaults to 29070)
        'address' => '192.0.2.1',

        // Name (with colors)
        // - Used on the landing page
        // - Also used as page title for the status page, if the request fails, or if "sv_hostname" cannot be read
        'name' => '^5M^7ain ^5S^7erver',

        // Character encoding used by the JKA server
        'charset' => 'Windows-1252',
    ],

    // Second JKA server
    [
        // URI for the status page
        'uri' => '/secondary-server',
        // If you have only 1 server, and you've disabled the landing page, you could use just '/'

        // IP address or domain name of the JKA server, with optional port (defaults to 29070)
        'address' => 'jka.example.com:29071',

        // Name (with colors)
        // - Used on the landing page
        // - Also used as page title for the status page, if the request fails, or if "sv_hostname" cannot be read
        'name' => '^3Secondary ^7Server',

        // Character encoding used by the JKA server
        'charset' => 'Windows-1252',
    ],

    // Other JKA servers...
];
