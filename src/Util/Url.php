<?php declare(strict_types=1);

namespace JkaServerStatus\Util;

class Url
{
    /**
     * Builds the full UDP URL from the JKA server IP address or domain, with optional port (defaults to 29070).
     * Does not check whether it's valid.
     * @param string $jkaServerAddress IP address or domain name, with optional port (e.g. "192.0.2.1" or "jka.example.com:29071")
     * @return string URL with scheme and port (defaults to 29070) (e.g. "udp://192.0.2.1:29070")
     */
    public static function buildFullUdpUrl(string $jkaServerAddress): string
    {
        $url = 'udp://' . $jkaServerAddress;
        if (!preg_match('/\:[0-9]{1,5}$/', $url)) {
            // The URL doesn't end with the port number
            $url .= ':29070'; // Add the default port
        }

        return $url;
    }
}
