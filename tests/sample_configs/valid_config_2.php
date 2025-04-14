<?php

$log_level = LOG_ERR;
$log_file = 'php://stdout';
$caching_delay = 9;
$timeout_delay = 2;
$asset_url = '/prefix';
$enable_landing_page = false;
$landing_page_uri = '/server-list';
$enable_about_page = true;
$about_page_uri = '/tell-me-about-it';
$about_page_title = 'Credits (and legal stuff)';
$canonical_url = 'https://example.com/';

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

$background_blur_radius = [
    'academy3' => 10, // Specify a value for a map that does NOT have a default value + test max value
    'default' => 2, // Override a map that DOES have a default (0)
    'vjun2' => 0, // Override (was: 7) + test minimum value
    // Several values aren't overridden and should stay to their default value (e.g. 'yavin1b' => 7)
];

$background_opacity = [
    'mp/ffa5' => 30, // Override a map that DOES have a default value (40)
    'academy6' => 0, // Specify a value for a map that does NOT have a default value + test min value
    'hoth2' => 100, // Override (was: 40) + test max value
    // Several values aren't overridden and should stay to their default value (e.g. 'yavin1b' => 40)
];
