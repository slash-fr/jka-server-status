<?php

/**
 * Builds the full URL for an asset (usually root-relative, depends on your $asset_url).
 * @param string $path Root-relative path to the asset (e.g. "/style.css")
 * @return string Full URL (e.g. "/prefix/style.css?version=2025-02-09--18-59-42")
 */
function asset(string $path): string
{
    global $templateHelper;
    return $templateHelper->asset($path);
}

/**
 * Escapes HTML special characters and replaces color codes
 * @param string $name e.g. "^1Hello ^7World! >>"
 * @return string e.g. '<span class="red">Hello </span><span class="white">World! &gt;&gt;</span>'
 */
function format_name(string $name): string
{
    global $templateHelper;
    return $templateHelper->formatName($name);
}

/**
 * Removes color codes from a name. Does NOT escape HTML special characters.
 * @param string $name e.g. "^1Hello ^7World! >>"
 * @return string e.g. "Hello World! >>"
 */
function strip_colors(string $name): string
{
    global $templateHelper;
    return $templateHelper->stripColors($name);
}
