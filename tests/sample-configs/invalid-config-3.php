<?php

$log_level = 0; // No logging
$log_file = '/dev/null'; // No, really, no logging
if (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0) {
    $log_file = 'NUL';
}

// Voluntary mistake: missing required field "address"
$jka_servers = [
    [
        'uri' => '/main-server',
        //'address' => '192.0.2.1',
        'name' => '^5M^7ain ^5S^7erver',
        'charset' => 'Windows-1252',
    ]
];