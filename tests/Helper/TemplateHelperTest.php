<?php declare(strict_types=1);

namespace JkaServerStatus\Tests\Template;

use JkaServerStatus\Config\ConfigData;
use JkaServerStatus\Helper\TemplateHelper;
use JkaServerStatus\Log\Logger;
use JkaServerStatus\Tests\Log\MockLogger;
use JkaServerStatus\Tests\TestCase;

final class TemplateHelperTest extends TestCase
{
    public function testAsset(): void
    {
        // Create file/folders we need
        $tempDir = sys_get_temp_dir() . '/JkaServerStatusTests';
        $tempPublicDir = $tempDir . '/public';
        if (!is_dir($tempPublicDir)) {
            $this->assertTrue(mkdir($tempPublicDir, 0755, true));
        }

        // /main.js
        $testJsFile = $tempPublicDir . '/main.js';
        // Let's pretend the file was updated at that particular time:
        $testJsUpdatedAt = strtotime('2025-02-09 18:59:42 UTC');
        touch($testJsFile, $testJsUpdatedAt);

        // Create the objects we need
        $config = new ConfigData(
            10,
            3,
            '',
            false,
            '/',
            false,
            '',
            '',
            [],
            ConfigData::DEFAULT_BACKGROUND_BLUR_RADIUS_PER_MAP,
            ConfigData::DEFAULT_BACKGROUND_OPACITY_PER_MAP,
            $tempDir
        );
        $logger = new MockLogger();
        $templateHelper = new TemplateHelper($config, $logger);

        $this->assertSame('/main.js?version=2025-02-09--18-59-42', $templateHelper->asset('/main.js'));
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        @unlink($testJsFile);
        @rmdir($tempPublicDir);
        @rmdir($tempDir);
    }

    public function testAssetInSubdir(): void
    {
        // Create file/folders we need
        $tempDir = sys_get_temp_dir() . '/JkaServerStatusTests';
        $tempPublicDir = $tempDir . '/public';
        $tempSubDir = $tempPublicDir . '/subfolder';
        if (!is_dir($tempSubDir)) {
            $this->assertTrue(mkdir($tempSubDir, 0755, true));
        }

        // /subfolder/main.css
        $testCssFile = $tempSubDir . '/main.css';
        // Let's pretend the file was updated at that particular time:
        $testCssUpdatedAt = strtotime('2025-03-14 12:34:56 UTC');
        touch($testCssFile, $testCssUpdatedAt);

        // Create the objects we need
        $config = new ConfigData(
            10,
            3,
            '',
            false,
            '/',
            false,
            '',
            '',
            [],
            ConfigData::DEFAULT_BACKGROUND_BLUR_RADIUS_PER_MAP,
            ConfigData::DEFAULT_BACKGROUND_OPACITY_PER_MAP,
            $tempDir
        );
        $logger = new MockLogger();
        $templateHelper = new TemplateHelper($config, $logger);

        $this->assertSame(
            '/subfolder/main.css?version=2025-03-14--12-34-56',
            $templateHelper->asset('/subfolder/main.css')
        );
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        @unlink($testCssFile);
        @rmdir($tempSubDir);
        @rmdir($tempPublicDir);
        @rmdir($tempDir);
    }

    public function testAssetWithPrefix(): void
    {
        // Create file/folders we need
        $tempDir = sys_get_temp_dir() . '/JkaServerStatusTests';
        $tempPublicDir = $tempDir . '/public';
        if (!is_dir($tempPublicDir)) {
            $this->assertTrue(mkdir($tempPublicDir, 0755, true));
        }

        // /test.txt
        $testFile = $tempPublicDir . '/test.txt';
        // Let's pretend the file was updated at that particular time:
        $testFileUpdatedAt = strtotime('2025-02-09 18:59:42 UTC');
        touch($testFile, $testFileUpdatedAt);

        // Create the objects we need
        $config = new ConfigData(
            10,
            3,
            '/prefix',
            false,
            '/',
            false,
            '',
            '',
            [],
            ConfigData::DEFAULT_BACKGROUND_BLUR_RADIUS_PER_MAP,
            ConfigData::DEFAULT_BACKGROUND_OPACITY_PER_MAP,
            $tempDir
        );
        $logger = new MockLogger();
        $templateHelper = new TemplateHelper($config, $logger);

        $this->assertSame('/prefix/test.txt?version=2025-02-09--18-59-42', $templateHelper->asset('/test.txt'));
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        @unlink($testFile);
        @rmdir($tempPublicDir);
        @rmdir($tempDir);
    }

    public function testFormatName(): void
    {
        $config = new ConfigData(10, 3, '', false, '/', false, '', '', []);
        $logger = new MockLogger();
        $templateHelper = new TemplateHelper($config, $logger);

        $this->assertSame(
            '<span class="white">' // The formatted names always start with this
            . 'Padawan'
            . '</span>', // The formatted names always end with this
            $templateHelper->formatName('Padawan')
        );

        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        $this->assertSame(
            '<span class="white">' // The formatted names always start with this
            . '</span><span class="red">Hello '
            . '</span><span class="white">World &gt; &gt;' // HTML escaping
            . '</span>', // The formatted names always end with this
            $templateHelper->formatName('^1Hello ^7World > >')
        );

        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        $this->assertSame(
            '<span class="white">' // The formatted names always start with this
            . '</span><span class="green">§</span><span class="black">ÑØW</span><span class="green">§'
            . '</span><span class="black">TØ®M</span><span class="green">¹'
            . '</span>', // The formatted names always end with this
            $templateHelper->formatName('^2§^0ÑØW^2§^0TØ®M^2¹')
        );

        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));
    }

    public function testStripColors(): void
    {
        $config = new ConfigData(10, 3, '', false, '/', false, '', '', []);
        $logger = new MockLogger();
        $templateHelper = new TemplateHelper($config, $logger);

        $this->assertSame('Padawan', $templateHelper->stripColors('Padawan'));
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        // No HTML escaping in stripColors()
        $this->assertSame('Hello World > >', $templateHelper->stripColors('^1Hello ^7World > >'));
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));

        $this->assertSame('§ÑØW§TØ®M¹', $templateHelper->stripColors('^2§^0ÑØW^2§^0TØ®M^2¹'));
        $this->assertCount(0, $logger->getMessages([Logger::ERROR, Logger::WARNING]));
    }
}
