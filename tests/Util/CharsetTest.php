<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Util;

use JkaServerStatus\Tests\TestCase;
use JkaServerStatus\Util\Charset;

final class CharsetTest extends TestCase
{
    public function testValidWindows1252(): void
    {
        $text = Charset::toUtf8("BEFORE \x80 AFTER", "Windows-1252");
        $this->assertSame('BEFORE € AFTER', $text);

        $text = Charset::toUtf8("^2\xA7^0\xD1\xD8W^2\xA7^0T\xD8\xAEM^2\xB9", "Windows-1252");
        $this->assertSame('^2§^0ÑØW^2§^0TØ®M^2¹', $text); // Actual username found in the wild
        // Thank you Snow for your fancy username ;)
        // It's great for character encoding tests.
    }

    public function testInvalidWindows1252(): void
    {
        $text = Charset::toUtf8("BEFORE \x81 AFTER", "Windows-1252");
        $this->assertSame('BEFORE  AFTER', $text);
    }

    public function testValidWindows1251(): void
    {
        $text = Charset::toUtf8("BEFORE \x80 AFTER", "Windows-1251");
        $this->assertSame('BEFORE Ђ AFTER', $text);
    }

    public function testInvalidUtf8(): void
    {
        $text = Charset::toUtf8("BEFORE \x80 AFTER", "UTF-8");
        $this->assertSame('BEFORE  AFTER', $text);
    }

    public function testValidUtf8(): void
    {
        $text = Charset::toUtf8("BEFORE é AFTER", "UTF-8");
        $this->assertSame('BEFORE é AFTER', $text);
    }
}
