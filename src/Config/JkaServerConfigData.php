<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

class JkaServerConfigData
{
    /** @var string $uri Status page URI */
    public readonly string $uri;

    /** @var string $address IP address or domain name of the JKA server (with optional port) */
    public readonly string $address;

    /** @var string $name JKA Server Name (supports colors) */
    public readonly string $name;

    /** @var string $subtitle Subtitle to display inside the server button on the landing pagee */
    public readonly string $subtitle;

    /** @var string $charset Charset to use when parsing the response */
    public readonly string $charset;

    /**
     * @param string $uri Status page URI
     * @param string $address IP address or domain name of the JKA server (with optional port)
     * @param string $name JKA Server Name (supports colors)
     * @param string $charset Charset to use when parsing the response
     */
    public function __construct(string $uri, string $address, string $name, string $subtitle, string $charset)
    {
        $this->uri = $uri;
        $this->address = $address;
        $this->name = $name;
        $this->subtitle = $subtitle;
        $this->charset = $charset;
    }
}