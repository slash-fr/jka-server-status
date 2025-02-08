<?php

// Example of a UDP server that sends INVALID responses.
// Usage:
// - Run `php jka-server-invalid-response.php`
// - In `config.php`, make one of the servers point to "127.0.0.1"

$socket = stream_socket_server('udp://127.0.0.1:29070', $error_code, $error_message, STREAM_SERVER_BIND);
if (!$socket) {
    die("ERROR $error_code: $error_message");
}

do {
    $packet = stream_socket_recvfrom($socket, 1, 0, $peer);
    echo "$peer\n";
    // The server is supposed to send a response starting with "\xFF\xFF\xFF\xFFstatusResponse\n"
    // Let's send the current date instead.
    stream_socket_sendto($socket, date('Y-m-d H:i:s'), 0, $peer);
} while ($packet !== false);
