<?php declare(strict_types=1);

namespace JkaServerStatus\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        error_reporting(E_ALL);
    }
}
