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

        $this->writePost('hopper.md', "type: quote\ntags: [Leben]\ndate: 2019-03-01\nsource: Grace Hopper\nsource_url: https://example.com/hopper\n+++\nThe most dangerous phrase.");
        $this->writePost('link.md', "type: link\ntags: [Internet]\ndate: 2019-04-02\nurl: https://www.example.org/read\ntitle: Worth reading\n+++\nA *short* note.");

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
            '201903/grace-hopper-the-most-dangerous-phrase.html',
            'tags/leben/index.html',
            '201904/worth-reading.html',
            'tags/internet/index.html',
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

        $post = file_get_contents($this->project . '/public/201906/faire-preise.html');
        $this->assertStringContainsString('<main id="content">', $post);
        $this->assertStringContainsString('<aside id="sidebar" aria-label="About Susy McFitzgerald">', $post);

        $hash = substr(hash_file('xxh3', $this->project . '/public/styles/styles.css'), 0, 8);
        $this->assertStringContainsString(
            'href="https://example.com/blog/styles/styles.css?v=' . $hash . '"',
            file_get_contents($this->project . '/public/201906/faire-preise.html')
        );

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

    public function testRendersQuotes() : void
    {
        [$exitCode, $output] = $this->build();
        $this->assertSame(0, $exitCode, $output);

        $page = file_get_contents($this->project . '/public/201903/grace-hopper-the-most-dangerous-phrase.html');
        $this->assertStringContainsString('<blockquote', $page);
        $this->assertStringContainsString('The most dangerous phrase.', $page);
        $this->assertStringContainsString('<a href="https://example.com/hopper">Grace Hopper</a>', $page);

        $feed = file_get_contents($this->project . '/public/feed.xml');
        $this->assertStringContainsString('&lt;blockquote&gt;', $feed);
        $this->assertStringContainsString('Grace Hopper', $feed);
    }

    public function testRendersLinks() : void
    {
        [$exitCode, $output] = $this->build();
        $this->assertSame(0, $exitCode, $output);

        $page = file_get_contents($this->project . '/public/201904/worth-reading.html');
        $this->assertStringContainsString('<a href="https://www.example.org/read">Worth reading</a>', $page);
        $this->assertStringContainsString('example.org', $page);
        $this->assertStringContainsString('A <em>short</em> note.', $page);

        $this->assertStringContainsString('Link: Worth reading', file_get_contents($this->project . '/public/archive/index.html'));

        $feed = file_get_contents($this->project . '/public/feed.xml');
        $this->assertStringContainsString('&lt;a href="https://www.example.org/read"&gt;Worth reading&lt;/a&gt; (example.org)', $feed);
    }

    public function testFailsOnLinkWithoutUrl() : void
    {
        $this->writePost('nowhere.md', "type: link\ndate: 2019-06-17\ntitle: Nowhere\n+++\nWhere to?");

        [$exitCode, $output] = $this->build();

        $this->assertNotSame(0, $exitCode);
        $this->assertStringContainsString('needs a "url: https://…"', $output);
    }

    public function testFailsOnQuoteWithoutSource() : void
    {
        $this->writePost('anonymous.md', "type: quote\ndate: 2019-06-17\n+++\nWho said this?");

        [$exitCode, $output] = $this->build();

        $this->assertNotSame(0, $exitCode);
        $this->assertStringContainsString('needs a "source: …"', $output);
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

    #[\PHPUnit\Framework\Attributes\RequiresFunction('imagettftext')]
    public function testGeneratesShareImages() : void
    {
        $theme = $this->project . '/themes/sprainNineteen';
        copy(dirname(__DIR__) . '/ShareImage/fixtures/inter.ttf', $theme . '/inter.ttf');
        file_put_contents($theme . '/config.yml', "\nshare_images:\n  font: inter.ttf\n  label: '{{ post.author }}'\n", FILE_APPEND);
        $this->writePost('bild.md', "date: 2019-05-01\nimage: https://example.com/own.jpg\n+++\n# Mit Bild\n\nText.");

        [$exitCode, $output] = $this->build();
        $this->assertSame(0, $exitCode, $output);

        $this->assertSame([1200, 630], array_slice(getimagesize($this->project . '/public/share/201906/faire-preise.png'), 0, 2));
        $this->assertStringContainsString(
            '<meta property="og:image" content="https://example.com/blog/share/201906/faire-preise.png">',
            file_get_contents($this->project . '/public/201906/faire-preise.html')
        );

        // A post with its own image keeps it
        $this->assertFileDoesNotExist($this->project . '/public/share/201905/mit-bild.png');
        $this->assertStringContainsString('content="https://example.com/own.jpg"', file_get_contents($this->project . '/public/201905/mit-bild.html'));
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
