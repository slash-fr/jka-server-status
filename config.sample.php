<?php

// Application-level logging configuration
$log_file = __DIR__ . '/var/log/server.log';
// Default value if omitted: __DIR__ . '/var/log/server.log'
// Make sure the file permissions allow PHP to write into that file.

$log_level = LOG_INFO;
// 0 => No logging
// LOG_INFO => Logs LOG_INFO messages and higher levels (LOG_WARNING and LOG_ERR)
// LOG_WARNING => Logs LOG_WARNING messages and higher levels (LOG_ERR)
// LOG_ERR => Only logs LOG_ERR messages (i.e. error messages)
// Default value if omitted: LOG_INFO

// Cache JKA server responses for a few seconds, to avoid sending an excessive amount of requests
$caching_delay = 10; // 10 seconds
// 0 => Disables caching (not recommended)
// Defaults to 10 seconds if omitted.
//
// Cached responses will be stored in the `cache` folder.
// => Make sure the file permissions allow PHP to write into that folder.

// How long to wait for a response from the JKA server
$timeout_delay = 3; // 3 seconds
// Minimum value: 1 second
// Defaults to 3 seconds if omitted.

// Prefix prepended to asset URLs (CSS, JS, images, ...)
// e.g. "/server-status/" if you're hosting the script in a subfolder of your actual web root
// Could also be your CDN's URL (e.g. "https://cdn.example.com/")
$asset_url = '/';
// Defaults to '/' if omitted.
// The trailing slash is optional (will be added automatically).

// Enable the landing page? (List of JKA servers)
$enable_landing_page = true; // If you have only 1 server, you should probably set it to `false`
// If omitted, the landing page will be enabled only if you have declared multiple $jka_servers below (not just one).
$landing_page_uri = '/'; // Defaults to '/' if omitted.

// "About" page
$enable_about_page = false; // Defaults to false
// If you enable the "About" page:
// - Copy: "templates/_config_page_content.default.php"
//     To: "templates/_config_page_content.php" (keep the leading underscore)
// - Write valid HTML. Don't worry, you don't really need to know PHP.
$about_page_uri = '/about'; // Defaults to '/about'
$about_page_title = 'About'; // Title of the "About" page (and "About" link). Defaults to 'About'.
// Depending on your jurisdiction, you may be required by law to host a "Legal notice" / "Impressum"
// (or similar) section on your website.
// The "About" page can be a good place to put it.

// Canonical (root) URL, used for OpenGraph tags (e.g. "og:url" and "og:image")
//$canonical_url = 'https://example.com/';
// Not set by default => OG tags are disabled by default

// JKA Server(s) (required)
$jka_servers = [
    // First JKA server
    [
        // URI for the status page
        'uri' => '/main-server',
        // Defaults to '/' if you've declared only 1 server (required otherwise).

        // IP address or domain name of the JKA server, with optional port (defaults to 29070)
        'address' => '192.0.2.1', // Required

        // Name (with colors)
        // - Used on the landing page
        // - Also used as page title for the status page, if the request fails, or if "sv_hostname" cannot be read
        'name' => '^5M^7ain ^5S^7erver',
        // Defaults to the value of the "address" field if omitted.

        // Subtitle to display inside the server button on the landing page
        'subtitle' => 'Server location: Germany', // Just text (no HTML, no color codes)
        // Totally optional, and the landing page looks better without it

        // Character encoding used by the JKA server
        'charset' => 'Windows-1252',
        // Defaults to "Windows-1252" if omitted.
    ],

    // Second JKA server
    [
        'uri' => '/secondary-server',
        'address' => 'jka.example.com:29071',
        'name' => '^3Secondary ^7Server',
        'subtitle' => 'Server location: USA',
        'charset' => 'Windows-1252',
    ],

    // Other JKA servers...
];

// Opacity per background image (i.e. per map)
// Usually 50%, but some images are brighter, or have more contrast, which hurts readability.
$background_opacity = [
    // Allowed range: [0-100]
    'mp/ctf2' => 40, // "mp/ctf2" defaults to 40 if omitted
    'mp/ctf5' => 40, // "mp/ctf5" defaults to 40 if omitted
    'mp/duel6' => 40, // "mp/duel6" defaults to 40 if omitted
    'mp/duel9' => 40, // "mp/duel9" defaults to 40 if omitted
    'mp/ffa5' => 40, // "mp/ffa5" defaults to 40 if omitted
    'mp/siege_desert' => 40, // "mp/siege_desert" defaults to 40 if omitted
    'mp/siege_hoth' => 40, // "mp/siege_hoth" defaults to 40 if omitted
    'mp/siege_korriban' => 30, // "mp/siege_korriban" defaults to 30 if omitted
    'academy3' => 30, // "academy3" defaults to 30 if omitted
    'academy4' => 30, // "academy4" defaults to 30 if omitted
    'hoth2' => 40, // "hoth2" defaults to 40 if omitted
    'kor2' => 40, // "kor2" defaults to 40 if omitted
    't1_sour' => 40, // "t1_sour" default to 40 if omitted
    't1_surprise' => 40, // "t1_surprise" default to 40 if omitted
    't2_dpred' => 40, // "t2_dpred" defaults to 40 if omitted
    't2_trip' => 40, // "t2_trip" defaults to 40 if omitted
    't2_wedge' => 40, // "t2_wedge" defaults to 40 if omitted
    't3_hevil' => 40, // "t3_hevil" defaults to 40 if omitted
    'taspir2' => 40, // "taspir2" defaults to 40 if omitted
    'vjun2' => 40, // "vjun2" defaults to 40 if omitted
    'yavin1' => 40, // "yavin1" defaults to 40 if omitted
    'yavin1b' => 40, // "yavin1b" defaults to 40 if omitted
    // Other levelshots default to 50
];
