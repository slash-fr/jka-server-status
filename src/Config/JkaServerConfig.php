<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

class JkaServerConfig
{
    /** @var string $uri Status page URI */
    public readonly string $uri;

    /** @var string $address IP address or domain name of the JKA server (with optional port) */
    public readonly string $address;

    /** @var string $name JKA Server Name (supports colors) */
    public readonly string $name;

    /** @var string $charset Charset to use when parsing the response */
    public readonly string $charset;

    public function __construct(string $uri, string $address, string $name, string $charset)
    {
        $this->uri = $uri;
        $this->address = $address;
        $this->name = $name;
        $this->charset = $charset;
    }
}