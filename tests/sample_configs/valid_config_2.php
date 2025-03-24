<?php

$log_level = LOG_ERR;
$log_file = 'php://stdout';
$caching_delay = 9;
$timeout_delay = 2;
$root_url = '/prefix';
$enable_landing_page = false;
$landing_page_uri = '/server-list';

$jka_servers = [
    [
        'uri' => '/main-server',
        'address' => '192.0.2.1',
        'name' => '^5M^7ain ^5S^7erver',
        'charset' => 'Windows-1252',
    ],
    [
        'uri' => '/secondary-server',
        'address' => 'jka.example.com:29071',
        'name' => '^3Secondary ^7Server',
        'subtitle' => '  Server location: Earth   ', // The leading/trailing spaces should get trimmed
        'charset' => 'UTF-8',
    ],
];
