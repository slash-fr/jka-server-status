<?php

$log_level = 0; // No logging
$log_file = '/dev/null'; // No, really, no logging
if (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0) {
    $log_file = 'NUL';
}

// Intentional mistake: should be a boolean (not a string)
$enable_landing_page = 'true';

$jka_servers = [
    [
        'address' => '127.0.0.1'
    ]
];
