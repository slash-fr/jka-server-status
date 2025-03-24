<?php

$jka_servers = [
    [
        'uri' => '/test',
        'address' => '192.0.2.1',
    ],
    [
        'uri' => '/test', // Intentional mistake: conflicts with the first server
        'address' => 'example.com',
    ],
];
