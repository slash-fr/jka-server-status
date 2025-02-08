<?php

// Application-level logging configuration
$log_level = LOG_INFO;
// 0 => No logging
// LOG_INFO => Logs LOG_INFO messages and higher levels (LOG_WARNING and LOG_ERR)
// LOG_WARNING => Logs LOG_WARNING messages and higher levels (LOG_ERR)
// LOG_ERR => Only logs LOG_ERR messages
// Default value if omitted: LOG_INFO
//
// Application-level messages will be stored in `log/server.log`
// => Make sure the file permissions allow PHP to write into that file.

// Cache JKA server responses for a few seconds, to avoid sending an excessive amount of requests
$caching_delay = 10; // 10 seconds
// 0 => Disables caching (not recommended)
// Defaults to 10 seconds if omitted.
//
// Cached responses will be stored in the `cache` folder.
// => Make sure the file permissions allow PHP to write into that folder.

// How long to wait for a response from the JKA server
$timeout_delay = 3; // 3 seconds
// Minimum value: 1 second
// Defaults to 3 seconds if omitted.

// Root URL, prepended to asset URLs
// e.g. "/server-status/" if you're hosting the script in a subfolder of your actual web root
$root_url = '/';
// Defaults to '/' if omitted.
// The trailing slash is optional (will be added automatically).

// Enable the landing page? (List of JKA servers)
$enable_landing_page = true; // If you have only 1 server, you should probably set it to `false`
// If omitted, the landing page will be enabled only if you have declared multiple $jka_servers below (not just one).
$landing_page_uri = '/'; // Defaults to '/' if omitted.

// JKA Server(s) (required)
$jka_servers = [
    // First JKA server
    [
        // URI for the status page
        'uri' => '/main-server',
        // Defaults to '/' if you've declared only 1 server (required otherwise).

        // IP address or domain name of the JKA server, with optional port (defaults to 29070)
        'address' => '192.0.2.1', // Required

        // Name (with colors)
        // - Used on the landing page
        // - Also used as page title for the status page, if the request fails, or if "sv_hostname" cannot be read
        'name' => '^5M^7ain ^5S^7erver',
        // Defaults to the value of the "address" field if omitted.

        // Character encoding used by the JKA server
        'charset' => 'Windows-1252',
        // Defaults to "Windows-1252" if omitted.
    ],

    // Second JKA server
    [
        'uri' => '/secondary-server',
        'address' => 'jka.example.com:29071',
        'name' => '^3Secondary ^7Server',
        'charset' => 'Windows-1252',
    ],

    // Other JKA servers...
];
