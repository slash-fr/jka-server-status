<?php

define('COLOR_REPLACEMENTS', [
    '^0' => '</span><span class="black">',
    '^1' => '</span><span class="red">',
    '^2' => '</span><span class="green">',
    '^3' => '</span><span class="yellow">',
    '^4' => '</span><span class="blue">',
    '^5' => '</span><span class="cyan">',
    '^6' => '</span><span class="magenta">',
    '^7' => '</span><span class="white">',
    '^8' => '</span><span class="orange">',
    '^9' => '</span><span class="gray">',
]);

define('GAME_TYPES', [
    0 => 'FFA',
    1 => 'Holocron FFA',
    2 => 'Jedi Master',
    3 => 'Duel',
    4 => 'Power Duel',
    5 => 'Single Player FFA',
    6 => 'Team FFA',
    7 => 'Siege',
    8 => 'CTF (Capture The Flag)',
    9 => 'CTY (Capture The Ysalamiri)',
]);

function check_config(): void {
    global $log_level;
    if (!isset($log_level)) {
        $log_level = LOG_INFO;
    } else if (!is_int($log_level) || !in_array($log_level, [0, LOG_INFO, LOG_WARNING, LOG_ERR])) {
        $log_level = LOG_INFO;
        log_message(LOG_WARNING, 'Config variable $log_level must be either: 0, LOG_INFO, LOG_WARNING, or LOG_ERR.');
    }

    global $caching_delay;
    if (!isset($caching_delay)) {
        $caching_delay = 10;
    } else if (!is_int($caching_delay)) {
        log_message(LOG_WARNING, 'Config variable $caching_delay must be an int.');
        $caching_delay = (int)$caching_delay;
    }

    if ($caching_delay < 0) {
        log_message(LOG_WARNING, 'Config variable $caching_delay must be >= 0');
        $caching_delay = 0;
    }

    global $timeout_delay;
    if (!isset($timeout_delay)) {
        $timeout_delay = 3;
    } else if (!is_int($timeout_delay)) {
        log_message(LOG_WARNING, 'Config variable $timing_delay must be an int.');
        $timeout_delay = (int)$timeout_delay;
    }

    if ($timeout_delay < 1) {
        log_message(LOG_WARNING, 'Config variable $timing_delay must be >= 1');
        $timeout_delay = 1;
    }

    global $root_url;
    if (!isset($root_url)) {
        $root_url = '';
    } else if (!is_string($root_url)) {
        log_message(LOG_WARNING, 'Config variable $root_url must be a string.');
        $root_url = (string)$root_url;
    }
    $root_url = rtrim($root_url, '/'); // Remove the trailing slash
    
    global $jka_servers;
    if (!isset($jka_servers)) {
        config_error('$jka_servers is required.');
    } else if (!is_array($jka_servers)) {
        config_error('$jka_servers must be an array.');
    }

    $nb_jka_servers = count($jka_servers);

    if ($nb_jka_servers < 1) {
        config_error('$jka_servers must contain at least 1 server.');
    }

    foreach ($jka_servers as &$jka_server) {
        if (!is_array($jka_server)) {
            config_error('$jka_servers must be an array of arrays.');
        }
        
        // URI
        if (!isset($jka_server['uri'])) {
            if ($nb_jka_servers > 1) {
                config_error('An "uri" is required for each server (when multiple servers are declared).');
            }
            $jka_server['uri'] = '/';
        } else if (!is_string($jka_server['uri'])) {
            config_error('The "uri" of each server must be a string.');
        }

        // Address
        if (!isset($jka_server['address'])) {
            config_error('Each server must specify an address');
        } else if (!is_string($jka_server['address'])) {
            config_error('The "address" of each server must be a string.');
        }

        $full_url = build_url($jka_server['address']);

        if (!filter_var($full_url, FILTER_VALIDATE_URL)) {
            config_error('"' . $jka_server['address'] . '" is not a valid server address');
        }

        // Name
        if (!isset($jka_server['name'])) {
            $jka_server['name'] = $jka_server['address'];
        } else if (!is_string($jka_server['name'])) {
            log_message(LOG_WARNING, 'Config: The "name" of each server must be a string.');
            $jka_server['name'] = (string)$jka_server['name'];
        }

        // Charset
        if (!isset($jka_server['charset'])) {
            $jka_server['charset'] = 'Windows-1252';
        }
        // Check whether the charset is known to iconv()
        $encoding_test = @iconv($jka_server['charset'], 'UTF-8//IGNORE', '123456');
        if (!$encoding_test) {
            config_error('Charset "' . $jka_server['charset'] . '" does not seem to be supported by iconv().');
        }
    }

    global $enable_landing_page;
    if (!isset($enable_landing_page)) {
        // By default, enable the landing page if multiple JKA servers are declared
        $enable_landing_page = ($nb_jka_servers > 1);
    } else if (!is_bool($enable_landing_page)) {
        log_message(LOG_WARNING, 'Config variable $enable_landing_page must be a boolean.');
        $enable_landing_page = (bool)$enable_landing_page;
    }

    global $landing_page_uri;
    if (!isset($landing_page_uri)) {
        $landing_page_uri = '/';
    } else if (!is_string($landing_page_uri)) {
        log_message(LOG_WARNING, 'Config variable $landing_page_uri must be a string.');
        $landing_page_uri = (string)$landing_page_uri;
    }
}

function config_error(string $error_message): void
{
    log_message(LOG_ERR, "Config error: $error_message");
    http_response_code(500);
    header('Content-type: text/plain');
    die('JKA Server Status: configuration error');
}

/**
 * Build the full URL from the IP or domain (with optional port number)
 * @param string $jka_server_address e.g. "jka.example.com"
 * @return string e.g. "udp://jka.example.com:29070"
 */
function build_url(string $jka_server_address): string
{
    $url = 'udp://' . $jka_server_address;
    if (!preg_match('/\:[0-9]{1,5}$/', $url)) {
        // The URL doesn't end with the port number
        $url .= ':29070'; // Add the default port
    }

    return $url;
}

/**
 * Escapes HTML special characters and replaces color codes
 * @param string $name e.g. "^1Hello ^7World! >>"
 * @return string e.g. '<span class="red">Hello </span><span class="white">World! &gt;&gt;</span>'
 */
function format_name(string $name): string
{
    $name = '<span class="white">' . htmlspecialchars($name, ENT_SUBSTITUTE, 'UTF-8') . '</span>';
    $name = str_replace(array_keys(COLOR_REPLACEMENTS), array_values(COLOR_REPLACEMENTS), $name);

    return $name;
}

/**
 * Removes color codes from a name. Does NOT escape HTML special characters.
 * @param string $name e.g. "^1Hello ^7World! >>"
 * @return string e.g. "Hello World! >>"
 */
function strip_colors(string $name): string
{
    $name = str_replace(array_keys(COLOR_REPLACEMENTS), '', $name);
    return $name;
}

/**
 * Sends the server status (as HTML) to the browser
 * @param string $jka_server_address JKA server IP or hostname, with optional port (defaults to 29070)
 *                                   -> e.g. "192.0.2.1", "example.com", "example.com:29070"
 * @param string $jka_server_name JKA Server name (used only if "sv_hostname" cannot be read)
 * @param string $jka_server_charset JKA Server charset (e.g. "ISO-8859-1", "UTF-8", ...). Defaults to "Windows-1252".
 */
function print_server_status(
    string $jka_server_address,
    string $jka_server_name,
    string $jka_server_charset = 'Windows-1252'
) {
    // Sanitize the host for use as a filename:
    $cached_file = __DIR__ . '/../cache/' . preg_replace('/[^a-z0-9]/', '-', strtolower($jka_server_address)) . '.html';
    $cached_at = @filemtime($cached_file);
    $caching_delay = $GLOBALS['caching_delay'] ?? 10; // Default to 10 seconds if not set
    if (time() < $cached_at + $caching_delay) { // Shorter than the configured delay (in seconds)
        log_message(LOG_INFO, "$jka_server_address - from cache");
        readfile($cached_file);
        exit;
    }

    // Query the JKA server and parse its response
    $query_result = query_jka_server($jka_server_address);
    $data = parse_data($query_result, $jka_server_charset);
    $data['address'] = $jka_server_address;
    $data['server_name'] = $data['cvars']['sv_hostname'] ?? $jka_server_name;
    $char_x80 = @iconv($jka_server_charset, 'UTF-8//IGNORE', "\x80");
    if ($char_x80) {
        // "Fix" the server name
        $data['server_name'] = ltrim($data['server_name'], $char_x80);
        // Some server owners prepend "\x80" bytes to the "sv_hostname" cvar,
        // ("\x80" is a euro sign in Windows-1252 encoding),
        // to get their server displayed at the top of the list.
    }

    if (!$data['is_up']) {
        log_message(LOG_ERR, $jka_server_address . ' - Status: ' . $data['status']);
    }

    log_message(
        LOG_INFO,
        $jka_server_address . ' - Generating HTML'
        . ' - Status: ' . $data['status']
        . ' - Name: "' . $data['server_name'] . '"'
        . ' - Map: "' . ($data['cvars']['mapname'] ?? '') . '"'
        . ' - Game type: ' . ($data['cvars']['g_gametype'] ?? '?') . ' (' . ($data['game_type'] ?? '?') . ')'
        . ' - Players: ' . ($data['nb_players'] ?? '?') . ' / ' . ($data['cvars']['sv_maxclients'] ?? '?')
        . ' - ' . ($data['nb_humans'] ?? '?') . ' human(s) + ' . ($data['nb_bots'] ?? '?') . ' bot(s)'
        // humans + bots
    );

    // Render and cache the HTML
    ob_start();
    require_once __DIR__ . '/template.php';
    $buffer = ob_get_clean();
    if ($caching_delay > 0) {
        file_put_contents($cached_file, $buffer);
    }

    // Send the HTML to the browser
    echo $buffer;
}

/**
 * Send a request to the JKA server, determine whether it was successful, and return the response.
 * @param string $host JKA server IP or hostname, with optional port (defaults to 29070)
 *                     -> e.g. "192.0.2.1", "example.com", "example.com:29070"
 * @return array Array with the following keys:
 *               "error" => boolean,
 *               "timeout" => boolean,
 *               "response" => string (present only if "error" is false)
 */
function query_jka_server(string $host): array
{
    $query_result = [
        'error' => true,
        'timeout' => false,
    ];

    $url = build_url($host);

    // 3 second timeout for the connect() system call (shouldn't be a problem for a UDP socket)
    $socket = @stream_socket_client($url, $error_code, $error_message, 3.0);
    if (!$socket) {
        log_message(LOG_ERR, "$host - Error code: $error_code - Error message: $error_message");
        return $query_result; // 'error' => true
    }

    // Timeout for reading over the socket
    $timeout_delay = (int)($GLOBALS['timeout_delay'] ?? 3); // Default to 3 seconds if not set
    stream_set_timeout($socket, $timeout_delay);

    @fwrite($socket, "\xFF\xFF\xFF\xFFgetstatus\n");
    $response = @fread($socket, 65535);
    if (!$response) {
        $metadata = stream_get_meta_data($socket);
        fclose($socket);
        if ($metadata['timed_out']) {
            $query_result['timeout'] = true;
        }
        return $query_result; // 'error' => true
    }

    fclose($socket);

    $query_result['error'] = false;
    $query_result['response'] = $response;

    return $query_result;
}

/**
 * Parse the server response
 * @param array $query_result Return value from query_jka_server()
 * @param string $jka_server_encoding Server charset (e.g. "ISO-8859-1", "UTF-8", ...). Defaults to "Windows-1252".
 * @return array UTF-8 encoded data. e.g. ['address' => 192.0.2.1, 'status' => 'Timeout']
 */
function parse_data(array $query_result, string $jka_server_encoding = 'Windows-1252'): array
{
    // Data that will be passed to "template.php"
    $data = [
        'is_up' => false,
        'status' => 'Error',
        'background_image_url' => $GLOBALS['root_url'] . '/levelshots/default.jpg',
        'default_background_image_url' => $GLOBALS['root_url'] . '/levelshots/default.jpg',
    ];

    if ($query_result['error']) {
        $data['status'] = $query_result['timeout'] ? 'Timeout' : 'Down';
        return $data;
    }

    $response = $query_result['response'];

    // Make sure line endings are only "\n"
    $response = str_replace("\r", "", $response);

    // Parse the output
    $lines = explode("\n", $response);
    $nb_lines = count($lines);
    if (
        $nb_lines < 2
        || $lines[0] !== "\xFF\xFF\xFF\xFFstatusResponse"
        || !str_starts_with($lines[1], "\\")
    ) {
        $data ['status'] = 'Error (invalid response)';
        return $data;
    }

    // Data that will be passed to "template.php"
    $data['is_up'] = true;
    $data['status'] = 'Up';
    $data['cvars'] = [];

    // Fix the encoding
    for ($i = 1; $i < $nb_lines; $i++) {
        $lines[$i] = @iconv($jka_server_encoding, 'UTF-8//IGNORE', $lines[$i]);
    }

    // Cvars (e.g. "\key1\value1\key2\value2...")
    $raw_server_info = explode("\\", $lines[1]);
    array_shift($raw_server_info); // Ignore the starting backslash
    $nb_fields = floor(count($raw_server_info) / 2);
    for ($i = 0; $i < $nb_fields; $i++) {
        $data['cvars'][$raw_server_info[2 * $i]] = $raw_server_info[2 * $i + 1];
    }

    // Sort cvars by cvar name
    ksort($data['cvars'], SORT_NATURAL | SORT_FLAG_CASE); // Sort by keys (case insensitive)

    if (isset($data['cvars']['g_gametype']) && isset(GAME_TYPES[(int)$data['cvars']['g_gametype']])) {
        // Readable name for the game type
        $data['game_type'] = GAME_TYPES[(int)$data['cvars']['g_gametype']];
    }

    // Background image:
    $map_name = strtolower($data['cvars']['mapname']) ?? 'default';
    $path_to_map_image = __DIR__ . '/../public/levelshots/' . $map_name . '.jpg';
    if (preg_match('/^[a-zA-z_0-9\/]+$/', $map_name) && file_exists($path_to_map_image)) {
        // If the file name is safe (no "..", no weird characters), and the file exists
        $data['background_image_url'] = $GLOBALS['root_url'] . '/levelshots/' . $map_name . '.jpg';
    }

    // Players
    $data['players'] = [];
    for ($i = 2; $i < $nb_lines; $i++) {
        if (!preg_match('/^([0-9]+)\s+([0-9]+)\s+(.+)$/', $lines[$i], $matches)) {
            continue;
        }
        $data['players'][] = [
            'name' => trim($matches[3], '"'),
            'score' => $matches[1],
            'ping' => $matches[2],
        ];
    }

    usort($data['players'], function ($player1, $player2) {
        // Sort by score (descending), then by ping (descending), then by name (alphabetical)
        if ((int)$player1['score'] > (int)$player2['score']) {
            return -1;
        } else if ((int)$player1['score'] < (int)$player2['score']) {
            return 1;
        }
        // Same score => Sort by ping
        if ((int)$player1['ping'] > (int)$player2['ping']) {
            return -1;
        } else if ((int)$player1['ping'] < (int)$player2['ping']) {
            return 1;
        }
        // Same score, same ping => Sort by name (case insensitive)
        return strcasecmp(strip_colors($player1['name']), strip_colors($player2['name']));
    });
    
    // Count players, bots and humans
    $data['nb_players'] = count($data['players']);
    $data['nb_bots'] = 0;
    $data['nb_humans'] = 0;
    foreach ($data['players'] as $player) {
        if (isset($player['ping']) && $player['ping'] == 0) {
            $data['nb_bots']++;
        } else {
            $data['nb_humans']++;
        }
    }

    return $data;
}

/**
 * Logs the specified message (prefixed by date/time) to the log file
 * @param int $level LOG_INFO, LOG_WARNING, or LOG_ERR
 * @param string $message The message to log
 */
function log_message(int $level, string $message): void
{
    $global_log_level = $GLOBALS['log_level'];
    if (!$global_log_level || $global_log_level < $level) {
        // Logging is disabled, or configured to log only more important messages
        return; // Don't log
    }

    $level_names = [
        LOG_INFO => 'INFO',
        LOG_WARNING => 'WARNING',
        LOG_ERR => 'ERROR',
    ];

    $level_string = $level_names[$level] ?? (string)$level; // If $level doesn't match $level_names, leave it in number form

    file_put_contents(
        __DIR__ . '/../log/server.log',
        date('Y-m-d H:i:s') . " - $level_string - $message\n",
        FILE_APPEND
    );
}
