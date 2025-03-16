<?php declare(strict_types=1);

namespace JkaServerStatus\Tests;

use JkaServerStatus\Util\Charset;
use PHPUnit\Framework\TestCase;

class CharsetTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(E_ALL);
    }

    public function testValidWindows1252(): void
    {
        $text = Charset::toUtf8("BEFORE \x80 AFTER", "Windows-1252");
        $this->assertSame('BEFORE € AFTER', $text);
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
