<?php

$log_level = 0; // No logging
$log_file = '/dev/null'; // No, really, no logging
if (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0) {
    $log_file = 'NUL';
}

// Intentional mistake: $jka_servers should be an array of arrays
$jka_servers = [
    'address' => '192.0.2.1'
];
