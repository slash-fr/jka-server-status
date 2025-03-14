<?php

$log_level = 0; // No logging
$log_file = '/dev/null'; // No, really, no logging
if (strcasecmp(PHP_OS_FAMILY, 'Windows') === 0) {
    $log_file = 'NUL';
}

// Voluntary typo: should be $jka_servers (with an "s")
$jka_server = [
    [
        'address' => '192.0.2.1'
    ]
];