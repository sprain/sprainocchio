<?php

namespace Sprainocchio\Tests\Post;

use PHPUnit\Framework\TestCase;
use Sprainocchio\Post\PostCreator;
use Symfony\Component\Finder\SplFileInfo;

class PostCreatorTest extends TestCase
{
    protected string $dir;

    protected function setUp() : void
    {
        $this->dir = sys_get_temp_dir() . '/sprainocchio-test-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown() : void
    {
        array_map('unlink', glob($this->dir . '/*'));
        rmdir($this->dir);
    }

    public function testReadsMetadataFromHeader() : void
    {
        $post = $this->createPost(<<<MD
            tags: [Business, Internet]
            date: 2019-06-17
            title: Header title
            summary: Header summary
            image: https://example.com/header.jpg
            slug: custom-slug
            +++
            # Body title

            Body text.
            MD);

        $this->assertSame('2019-06-17', $post->getDate()->format('Y-m-d'));
        $this->assertSame(['Business', 'Internet'], $post->getTags());
        $this->assertSame('Header title', $post->getTitle());
        $this->assertSame('Header summary', $post->getSummary());
        $this->assertSame('https://example.com/header.jpg', $post->getImage());
        $this->assertSame('201906' . DIRECTORY_SEPARATOR . 'custom-slug.html', $post->getFilename());
    }

    public function testFallsBackToContent() : void
    {
        $post = $this->createPost(<<<MD
            date: 2019-01-13
            +++
            # iOS-Shortcut für die nächste öV-Verbindung

            First paragraph.

            ![Image](https://example.com/image.png)
            MD);

        $this->assertSame('iOS-Shortcut für die nächste öV-Verbindung', $post->getTitle());
        $this->assertSame('First paragraph.', $post->getSummary());
        $this->assertSame('https://example.com/image.png', $post->getImage());
        $this->assertSame([], $post->getTags());
        $this->assertSame('201901' . DIRECTORY_SEPARATOR . 'ios-shortcut-fuer-die-naechste-oev-verbindung.html', $post->getFilename());
    }

    public function testKeepsSeparatorInBody() : void
    {
        $post = $this->createPost(<<<MD
            date: 2019-01-13
            +++
            # Title

            C+++ is not a language.
            MD);

        $this->assertStringContainsString('C+++ is not a language.', $post->getContent());
    }

    public function testRequiresDate() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('post.md needs a "date: YYYY-MM-DD"');

        $this->createPost(<<<MD
            tags: [Business]
            +++
            # Title
            MD);
    }

    public function testRequiresHeader() : void
    {
        $this->expectException(\RuntimeException::class);

        $this->createPost("# Title without header\n");
    }

    protected function createPost(string $content) : \Sprainocchio\Post\Post
    {
        file_put_contents($this->dir . '/post.md', $content);

        return (new PostCreator(new \Parsedown()))->fromFile(
            new SplFileInfo($this->dir . '/post.md', '', 'post.md')
        );
    }
}
