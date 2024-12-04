<?php

require_once __DIR__ . '/../src/functions.php';

// Path to QStat binary:
//define('QSTAT_BINARY', 'quakestat'); // If "quakestat" is in your PATH
define('QSTAT_BINARY', __DIR__ . '/../bin/qstat'); // If "qstat" is in the "bin" folder

// Root URL (trailing slash required):
define('ROOT_URL', '/'); // e.g. "/server-status/" if you're hosting the script in a subfolder of your actual web root

if ($_SERVER['REQUEST_URI'] === '/') {
    require_once __DIR__ . '/../src/landing-page.php';
} else if ($_SERVER['REQUEST_URI'] === '/main-server') {
    // You could also use query strings, such as: "?server=main"
    //if (isset($_GET['server']) && $_GET['server'] === 'main') { /* ... */ }

    print_server_status(
        '192.0.2.1' // Host IP address or domain name, and optionally, port number (defaults to 29070)
        // WARNING: for security reasons, do not pass raw query parameters
        // (or any other user input) as the $host parameter of print_server_status()
    );
} else if ($_SERVER['REQUEST_URI'] === '/secondary-server') {
    print_server_status(
        'jka.example.com:29071' // Host IP address or domain name, and optionally, port number (defaults to 29070)
        // WARNING: for security reasons, do not pass raw query parameters
        // (or any other user input) as the $host parameter of print_server_status()
    );
} else {
    http_response_code(404);
    require_once __DIR__ . '/../src/404.php';
}
