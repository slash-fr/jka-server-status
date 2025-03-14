<?php

// Example of a UDP server that sends a valid response.
// Usage:
// - Run `php jka-server-valid-response.php`
// - In `config.php`, make one of the servers point to "127.0.0.1"

$socket = stream_socket_server('udp://127.0.0.1:29070', $error_code, $error_message, STREAM_SERVER_BIND);
if (!$socket) {
    die("ERROR $error_code: $error_message");
}

do {
    $packet = stream_socket_recvfrom($socket, 1, 0, $peer);
    echo "$peer\n";
    stream_socket_sendto($socket, file_get_contents(__DIR__ . '/sample-response.txt'), 0, $peer);
    // WARNING: be careful when editing "sample-response.txt"
    // => It MUST start with "\xFF\xFF\xFF\xFF" bytes
    // => Which is invalid UTF-8, but looks like "ÿÿÿÿ" in Windows-1252 encoding
} while ($packet !== false);
