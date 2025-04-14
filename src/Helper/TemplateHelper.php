<?php declare(strict_types=1);

namespace JkaServerStatus\Helper;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Log\LoggerInterface;
use RuntimeException;

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
     * Builds the full URL for an asset (usually root-relative, depends on your $asset_url).
     * @param string $path Root-relative path to the asset (e.g. "/style.css")
     * @return string Full URL (e.g. "/prefix/style.css?version=2025-02-09--18-59-42")
     */
    public function asset(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            $path = "/$path";
        }

        $fullPath = $this->config->assetUrl . $path;

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
                'Could not read the file modification time, to build the query string for '
                . 'TemplateHelper::asset("' . $path . '"). Defaulting to "?version=1".'
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

    /**
     * @return bool True if OpenGraph tags are enabled, false otherwise
     */
    public function isOpenGraphEnabled(): bool
    {
        return isset($this->config->canonicalUrl);
    }

    /**
     * @return string The value for the "content" attribute of the "og:url" tag.
     * 
     * @throws RuntimeException if this method is called when OpenGraph tags are disabled / not configured.
     */
    public function getOgUrl(): string
    {
        if (!$this->isOpenGraphEnabled()) {
            throw new RuntimeException(
                __METHOD__ . ' must not be called when OpenGraph tags are disabled / not configured.'
            );
        }

        return $this->config->canonicalUrl . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string The value for the "content" attribute of the "og:image" tag.
     * 
     * @throws RuntimeException if this method is called when OpenGraph tags are disabled / not configured.
     */
    public function getOgImageUrl(): string
    {
        if (!$this->isOpenGraphEnabled()) {
            throw new RuntimeException(
                __METHOD__ . ' must not be called when OpenGraph tags are disabled / not configured.'
            );
        }

        $ogImageAssetUrl = $this->asset('/og-image.jpg');
        
        if (str_starts_with($ogImageAssetUrl, 'https://') || str_starts_with($ogImageAssetUrl, 'http://')) {
            // The $asset_url prefix may contain an absolute URL (might be a CDN, for instance)
            return $ogImageAssetUrl;
        }

        // If not, prefix the image URL by the canonical URL
        return $this->config->canonicalUrl . $ogImageAssetUrl;
    }
}
