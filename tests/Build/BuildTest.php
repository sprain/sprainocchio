<?php

namespace Sprainocchio\Tests\Build;

use PHPUnit\Framework\TestCase;

// Builds a small blog project with bin/sprainocchio, the way a blog that requires sprainocchio does
class BuildTest extends TestCase
{
    protected string $project;

    protected function setUp() : void
    {
        $this->project = sys_get_temp_dir() . '/sprainocchio-build-' . uniqid();
        mkdir($this->project . '/posts', 0777, true);
        mkdir($this->project . '/static/media', 0777, true);

        $this->copyDirectory(dirname(__DIR__, 2) . '/themes/sprainNineteen', $this->project . '/themes/sprainNineteen');

        file_put_contents($this->project . '/config.yml', <<<YML
            theme: sprainNineteen
            baseUrl: https://example.com/blog/
            title: Testblog
            redirects:
              old/page.html: 201906/faire-preise.html
            YML);

        file_put_contents($this->project . '/static/media/image.png', 'png');

        $this->writePost('faire-preise.md', "tags: [Business]\ndate: 2019-06-17\n+++\n# Faire Preise\n\nText.");
        $this->writePost('ueber.md', "tags: [Über uns]\ndate: 2019-01-13\nauthor: Claude\n+++\n# Für alle\n\nText.");

        mkdir($this->project . '/pages');
        file_put_contents($this->project . '/pages/about.md', "slug: ueber\n+++\n# Über diesen Blog\n\nText.");
    }

    protected function tearDown() : void
    {
        $this->removeDirectory($this->project);
    }

    public function testBuildsBlog() : void
    {
        [$exitCode, $output] = $this->build();
        $this->assertSame(0, $exitCode, $output);

        foreach ([
            'index.html',
            'archive/index.html',
            '201906/faire-preise.html',
            '201901/fuer-alle.html',
            'tags/business/index.html',
            'tags/ueber-uns/index.html',
            'styles/styles.css',
            'media/image.png',
            'old/page.html',
            'ueber.html',
            'feed.xml',
        ] as $file) {
            $this->assertFileExists($this->project . '/public/' . $file);
        }

        $this->assertStringContainsString(
            'url=https://example.com/blog/201906/faire-preise.html',
            file_get_contents($this->project . '/public/old/page.html')
        );

        $this->assertStringContainsString('<h1>Über diesen Blog</h1>', file_get_contents($this->project . '/public/ueber.html'));
        $this->assertStringContainsString('by Claude', file_get_contents($this->project . '/public/201901/fuer-alle.html'));
        $this->assertStringNotContainsString('by ', file_get_contents($this->project . '/public/201906/faire-preise.html'));

        $feed = file_get_contents($this->project . '/public/feed.xml');
        $this->assertStringContainsString('<title>Testblog</title>', $feed);
        $this->assertStringContainsString('<id>https://example.com/blog/201906/faire-preise.html</id>', $feed);
        $this->assertStringContainsString('<name>Claude</name>', $feed);
        $this->assertStringNotContainsString('Über diesen Blog', $feed, 'Pages are not posts');
        $this->assertStringContainsString(
            'href="https://example.com/blog/feed.xml"',
            file_get_contents($this->project . '/public/index.html')
        );

        $this->assertStringContainsString(
            'href="https://example.com/blog/tags/ueber-uns/"',
            file_get_contents($this->project . '/public/201901/fuer-alle.html')
        );
    }

    public function testFailsOnDuplicateUrls() : void
    {
        $this->writePost('copy.md', "date: 2019-06-17\nslug: faire-preise\n+++\n# Copy\n");

        [$exitCode, $output] = $this->build();

        $this->assertNotSame(0, $exitCode);
        $this->assertStringContainsString('would both be saved as 201906/faire-preise.html', $output);
    }

    public function testBuildsWithoutPagesFolder() : void
    {
        $this->removeDirectory($this->project . '/pages');

        [$exitCode, $output] = $this->build();

        $this->assertSame(0, $exitCode, $output);
    }

    public function testFailsOnPageNamedIndex() : void
    {
        file_put_contents($this->project . '/pages/home.md', "slug: index\n+++\n# Home\n");

        [$exitCode, $output] = $this->build();

        $this->assertNotSame(0, $exitCode);
        $this->assertStringContainsString('cannot be saved as index.html', $output);
    }

    protected function build() : array
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sprainocchio') . ' 2>&1';
        exec('cd ' . escapeshellarg($this->project) . ' && ' . $command, $output, $exitCode);

        return [$exitCode, implode("\n", $output)];
    }

    protected function writePost(string $name, string $content) : void
    {
        file_put_contents($this->project . '/posts/' . $name, $content);
    }

    protected function copyDirectory(string $src, string $dest) : void
    {
        mkdir($dest, 0777, true);
        foreach (new \DirectoryIterator($src) as $f) {
            if ($f->isFile()) {
                copy($f->getRealPath(), $dest . '/' . $f->getFilename());
            } elseif (!$f->isDot() && $f->isDir()) {
                $this->copyDirectory($f->getRealPath(), $dest . '/' . $f->getFilename());
            }
        }
    }

    protected function removeDirectory(string $dir) : void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($dir);
    }
}
