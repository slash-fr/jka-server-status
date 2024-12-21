<?php

$color_replacements = [
    '^0' => '</span><span class="mono black">',
    '^1' => '</span><span class="mono red">',
    '^2' => '</span><span class="mono green">',
    '^3' => '</span><span class="mono yellow">',
    '^4' => '</span><span class="mono blue">',
    '^5' => '</span><span class="mono cyan">',
    '^6' => '</span><span class="mono magenta">',
    '^7' => '</span><span class="mono white">',
    '^8' => '</span><span class="mono orange">',
    '^9' => '</span><span class="mono gray">',
];

/**
 * Escapes HTML special characters and replaces color codes
 * @param string $name e.g. "^1Hello ^7World! >>"
 * @return string e.g. '<span class="mono red">Hello </span><span class="mono white">World! &gt;&gt;</span>'
 */
function format_name(string $name): string
{
    global $color_replacements;
    $name = '<span class="mono white">' . htmlspecialchars($name) . '</span>';
    $name = str_replace(array_keys($color_replacements), array_values($color_replacements), $name);
    return $name;
}

/**
 * Removes color codes from a name. Does NOT escape HTML special characters.
 * @param string $name e.g. "^1Hello ^7World! >>"
 * @return string e.g. "Hello World! >>"
 */
function strip_colors(string $name): string
{
    global $color_replacements;
    $name = str_replace(array_keys($color_replacements), '', $name);
    return $name;
}

/**
 * @param string $qstat_output e.g. "JK3S\\192.0.2.1\\DOWN"
 * @return array e.g. ['address' => 192.0.2.1, 'status' => 'DOWN']
 */
function parse_data(string $qstat_output): array
{
    // Fix the encoding
    $qstat_output = iconv('Windows-1252', 'UTF-8', $qstat_output);

    // Make sure line endings are only "\n"
    $qstat_output = str_replace("\r", "", $qstat_output);

    // Parse the output
    $lines = explode("\n", $qstat_output);
    $nb_lines = count($lines);
    if ($nb_lines < 1) { // No data
        return ['status' => 'ERROR'];
    }

    $server_data = explode("\\", $lines[0]);
    $nb_server_fields = count($server_data);
    if ($nb_server_fields < 3 || $server_data[0] !== 'JK3S') {
        return ['status' => 'ERROR'];
    }

    $data = ['address' => $server_data[1]];

    if ($nb_server_fields < 6) { // No map / player data
        $data['status'] = $server_data[2]; // e.g. "DOWN" or "TIMEOUT"
        return $data;
    }

    // We've got map + player data, which means the server is up
    $data['status'] = 'UP';
    $data['server_name'] = $server_data[2];
    $data['map'] = $server_data[3];
    $data['nb_players'] = $server_data[5];
    $data['max_players'] = $server_data[4];
    $data['players'] = [];

    for ($i = 1; $i < $nb_lines; $i++) {
        $player_data = explode("\\", $lines[$i]);
        if (count($player_data) < 3) { // Invalid line
            continue; // Skip this line
        }
        $data['players'][] = [
            'name' => $player_data[0],
            'score' => $player_data[1],
            'ping' => $player_data[2],
        ];
    }

    usort($data['players'], function ($player1, $player2) {
        // Sort by score (descending), then by ping (descending), then by name (alphabetical)
        if ($player1['score'] > $player2['score']) {
            return -1;
        } else if ($player1['score'] < $player2['score']) {
            return 1;
        }
        // Same score
        if ($player1['ping'] > $player2['ping']) {
            return -1;
        } else if ($player1['ping'] < $player2['ping']) {
            return 1;
        }
        // Same score, same ping
        return $player1['name'] <=> $player2['name']; // Sort by name
    });

    return $data;
}

function print_server_status(string $host)
{
    // Sanitize the host for use as a filename:
    $cached_file = __DIR__ . '/../cache/' . preg_replace('/[^a-z0-9]/', '-', strtolower($host)) . '.html';
    $cached_at = @filemtime($cached_file);
    if (time() < $cached_at + 10) { // Cached less than 10 seconds ago
        readfile($cached_file);
        exit;
    }

    $host_arg = escapeshellarg($host);

    // Query the server
    // Use the "raw" mode, because the "xml" mode removes Windows-1252 special characters
    // (*Certain players* use names such as "^2§^0now^2¹" 😜)
    $qstat_output = shell_exec(QSTAT_BINARY . " -jk3s $host_arg -P -sort F -utf8 -raw \\");

    $data = parse_data($qstat_output);
    if (empty($data['address'])) {
        $data['address'] = $host;
    }

    // Background image:
    $background_image_url = $default_background_image_url = ROOT_URL . 'levelshots/default.jpg';

    $map_name = $data['map'] ?? 'default';
    $path_to_map_image = __DIR__ . '/../public/levelshots/' . $map_name . '.jpg';

    if (preg_match('/^[a-zA-z_0-9\/]+$/', $map_name) && file_exists($path_to_map_image)) {
        // If the file name is safe (no "..", no weird characters), and the file exists
        $background_image_url = ROOT_URL . 'levelshots/' . $map_name . '.jpg';
    }
    
    // Count bots and humans
    $nb_bots = 0;
    $nb_humans = 0;
    $players = $data['players'] ?? [];
    foreach ($players as $player) {
        if (isset($player['ping']) && $player['ping'] == 0) {
            $nb_bots++;
        } else {
            $nb_humans++;
        }
    }

    // Render and cache the HTML
    ob_start();
    require_once __DIR__ . '/template.php';
    $buffer = ob_get_clean();
    file_put_contents($cached_file, $buffer);

    // Send the HTML to the browser
    echo $buffer;
}