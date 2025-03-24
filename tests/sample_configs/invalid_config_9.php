<?php

// Intentional mistake: a 'uri' is required when there are multiple servers
$jka_servers = [
    [
        //'uri' => '/main-server',
        'address' => '192.0.2.1',
    ],
    [
        //'uri' => '/secondary-server',
        'address' => 'example.com',
    ],
];
