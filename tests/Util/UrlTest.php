<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Util;

use JkaServerStatus\Tests\TestCase;
use JkaServerStatus\Util\Url;

final class UrlTest extends TestCase
{
    public function testBuildFullUdpUrl(): void
    {
        $this->assertSame('udp://192.0.2.1:29070', Url::buildFullUdpUrl('192.0.2.1'));
        $this->assertSame('udp://192.0.2.1:29071', Url::buildFullUdpUrl('192.0.2.1:29071'));
        $this->assertSame('udp://jka.example.com:29070', Url::buildFullUdpUrl('jka.example.com'));
        $this->assertSame('udp://jka.example.com:29071', Url::buildFullUdpUrl('jka.example.com:29071'));
    }
}
