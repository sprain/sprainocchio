<?php

namespace Sprainocchio\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Sprainocchio\Twig\AssetExtension;

class AssetExtensionTest extends TestCase
{
    protected string $dir;

    protected function setUp() : void
    {
        $this->dir = sys_get_temp_dir() . '/sprainocchio-asset-' . uniqid();
        mkdir($this->dir . '/styles', 0777, true);
        file_put_contents($this->dir . '/styles/styles.css', 'body { color: red; }');
    }

    protected function tearDown() : void
    {
        @unlink($this->dir . '/styles/styles.css');
        @rmdir($this->dir . '/styles');
        @rmdir($this->dir);
    }

    public function testAddsContentHash() : void
    {
        $url = (new AssetExtension($this->dir . '/', 'https://example.com/blog/'))->asset('/styles/styles.css');

        $hash = substr(hash('xxh3', 'body { color: red; }'), 0, 8);
        $this->assertSame('https://example.com/blog/styles/styles.css?v=' . $hash, $url);
    }

    public function testVersionChangesWithContent() : void
    {
        $before = (new AssetExtension($this->dir, 'https://example.com/'))->asset('styles/styles.css');
        file_put_contents($this->dir . '/styles/styles.css', 'body { color: blue; }');
        $after = (new AssetExtension($this->dir, 'https://example.com/'))->asset('styles/styles.css');

        $this->assertNotSame($before, $after);
    }

    public function testFailsOnMissingFile() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("asset('styles/missing.css')");

        (new AssetExtension($this->dir, 'https://example.com/'))->asset('styles/missing.css');
    }
}
