<?php

$enable_about_page = true;
$about_page_uri = '/about';

$jka_servers = [
    [
        'uri' => '/about', // Intentional mistake: Conflicts with the "About" page URI
        'address' => '192.0.2.1',
    ]
];
