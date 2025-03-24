<?php

$enable_landing_page = true;
$landing_page_uri = '/test';

$jka_servers = [
    [
        'uri' => '/test', // Intentional mistake: conflicts with $landing_page_uri
        'address' => '192.0.2.1',
    ]
];
