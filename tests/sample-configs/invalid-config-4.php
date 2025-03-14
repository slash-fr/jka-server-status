<?php

// Intentional mistake: should be an int (not a string)
$log_level = 'LOG_INFO';
$log_file = '/dev/null'; // No actual logging
if (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0) {
    $log_file = 'NUL';
}

$jka_servers = [
    [
        'address' => '127.0.0.1'
    ]
];
