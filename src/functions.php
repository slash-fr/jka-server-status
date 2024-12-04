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

function print_server_status(string $host)
{
    // Sanitize the host for use as a filename:
    $cached_file = __DIR__ . '/../cache/' . preg_replace('/[^a-z0-9]/', '-', strtolower($host)) . '.html';
    $cached_at = @filemtime($cached_file);
    if (time() < $cached_at + 10) { // Cached less than 10 seconds ago
        readfile($cached_file);
        exit;
    }

    $host = escapeshellarg($host);

    // Query the server
    $xml = shell_exec(QSTAT_BINARY . " -jk3s $host -P -sort F -utf8 -xml");

    // Fix the encoding
    $xml = iconv('Windows-1252', 'UTF-8', $xml);

    // Parse the XML
    $qstat = new SimpleXMLElement($xml);

    // Background image:
    $background_image_url = $default_background_image_url = ROOT_URL . 'levelshots/default.jpg';

    $map_name = $qstat->server->map;
    $path_to_map_image = __DIR__ . '/../public/levelshots/' . $map_name . '.jpg';

    if (preg_match('/^[a-zA-z_0-9\/]+$/', $map_name) && file_exists($path_to_map_image)) {
        // If the file name is safe (no "..", no weird characters), and the file exists
        $background_image_url = ROOT_URL . 'levelshots/' . $map_name . '.jpg';
    }
    
    // Count bots and humans
    $nb_bots = 0;
    $nb_humans = 0;
    $players = $qstat->server->players->player ?? [];
    foreach ($players as $player) {
        if (isset($player->ping) && $player->ping == 0) {
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