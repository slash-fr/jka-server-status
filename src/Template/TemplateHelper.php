<?php declare(strict_types=1);

namespace JkaServerStatus\Template;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Log\LoggerInterface;

/**
 * Template-related methods
 */
class TemplateHelper
{
    private const COLOR_REPLACEMENTS = [
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
    ];

    private readonly ConfigData $config;
    private readonly LoggerInterface $logger;

    public function __construct(ConfigData $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Builds the full URL for an asset.
     * @param string $path Root-relative path to the asset (e.g. "/style.css")
     * @return string Full root-relative URL (e.g. "/prefix/style.css?version=2025-02-09--18-59-42")
     */
    public function asset(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            $path = "/$path";
        }

        $fullPath = $this->config->rootUrl . $path;

        $separator = '?';

        if (str_contains($fullPath, '?')) {
            $separator = '&';
        }

        return $fullPath . $separator . 'version=' . $this->getVersionString($path);
    }

    /**
     * Returns a version string built from the file modification date/time
     * (or "1" if the date/time cannot be determined).
     * @var string $path Root-relative path to the asset (e.g. "/style.css")
     * @return string e.g. "2025-02-09--18-59-42"
     */
    private function getVersionString(string $path): string
    {
        $updatedAt = filemtime($this->config->projectDir . '/public' . $path); // Timestamp (int)

        if (!$updatedAt) {
            $this->logger->warning(
                'Could not read the file modification time, to build the query string for asset("' . $path . '"). '
                . ' Defaulting to "?version=1"'
            );
            return '1';
        }

        // Human-readable version string
        return date('Y-m-d--H-i-s', $updatedAt); // e.g. "2025-02-09--18-59-42"
    }

    /**
     * Escapes HTML special characters and replaces color codes
     * @param string $name e.g. "^1Hello ^7World! >>"
     * @return string e.g. '<span class="red">Hello </span><span class="white">World! &gt;&gt;</span>'
     */
    public function formatName(string $name): string
    {
        $name = '<span class="white">' . htmlspecialchars($name, ENT_SUBSTITUTE, 'UTF-8') . '</span>';
        $name = str_replace(array_keys(self::COLOR_REPLACEMENTS), array_values(self::COLOR_REPLACEMENTS), $name);

        return $name;
    }

    /**
     * Removes color codes from a name. Does NOT escape HTML special characters.
     * @param string $name e.g. "^1Hello ^7World! >>"
     * @return string e.g. "Hello World! >>"
     */
    public function stripColors(string $name): string
    {
        $name = str_replace(array_keys(self::COLOR_REPLACEMENTS), '', $name);
        return $name;
    }
}
