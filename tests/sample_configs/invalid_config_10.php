<?php

$jka_servers = [
    [
        'uri' => '/main-server',
        'address' => '192.0.2.1',
    ],
    [
        // Intentional mistake: the 'uri' must be a string
        'uri' => 42,
        'address' => 'example.com',
    ],
];
