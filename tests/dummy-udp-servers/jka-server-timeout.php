<?php

// Example of a UDP server that times out.
// Usage:
// - Run `php jka-server-timeout.php`
// - In `config.php`, make one of the servers point to "127.0.0.1"

$socket = stream_socket_server('udp://127.0.0.1:29070', $error_code, $error_message, STREAM_SERVER_BIND);
if (!$socket) {
    die("ERROR $error_code: $error_message");
}

do {
    $packet = stream_socket_recvfrom($socket, 1, 0, $peer);
    echo "$peer\n";
    // Do not send anything to the UDP client
} while ($packet !== false);

// If you want to test the "down" status (rather than "timeout"), you don't need to run a UDP server.
// Just point your `config.php` to an unopened port, e.g. "127.0.0.1:1234".
