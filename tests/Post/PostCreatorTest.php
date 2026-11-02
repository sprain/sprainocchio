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
            author: Claude
            coauthor: Someone
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
        $this->assertSame('Claude', $post->getAuthor());
        $this->assertSame('Someone', $post->getMeta()['coauthor']);
        $this->assertSame('custom-slug', $post->getMeta()['slug']);
    }

    public function testDateIsOptionalForPages() : void
    {
        file_put_contents($this->dir . '/page.md', "title: About\n+++\nText.");

        $page = (new PostCreator(new \Parsedown()))->fromFile(
            new SplFileInfo($this->dir . '/page.md', '', 'page.md'),
            false
        );

        $this->assertNull($page->getDate());
        $this->assertNull($page->getAuthor());
        $this->assertSame(['title' => 'About'], $page->getMeta());
        $this->assertSame('About', $page->getTitle());
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

    public function testPostsAreArticlesByDefault() : void
    {
        $post = $this->createPost("date: 2019-01-13\n+++\n# Title\n\nText.");

        $this->assertSame('article', $post->getType());
        $this->assertNull($post->getSource());
    }

    public function testReadsQuote() : void
    {
        $post = $this->createPost(<<<MD
            type: quote
            date: 2026-11-02
            tags: [Leben]
            source: Grace Hopper
            source_url: https://example.com/hopper
            description: Why I *like* this one
            +++
            The most dangerous phrase in the language is "We've always done it this way."
            MD);

        $this->assertSame('quote', $post->getType());
        $this->assertSame('Grace Hopper', $post->getSource());
        $this->assertSame('https://example.com/hopper', $post->getSourceUrl());
        $this->assertSame('<p>Why I <em>like</em> this one</p>', $post->getDescription());
        $this->assertSame('Grace Hopper', $post->getTitle());
        $this->assertStringContainsString('The most dangerous phrase', $post->getContent());
        $this->assertStringContainsString('The most dangerous phrase', $post->getSummary());
        $this->assertSame(
            '202611' . DIRECTORY_SEPARATOR . 'grace-hopper-the-most-dangerous-phrase-in-the.html',
            $post->getFilename()
        );
    }

    public function testQuoteKeepsTitleAndSlugFromHeader() : void
    {
        $post = $this->createPost("type: quote\ndate: 2026-11-02\nsource: Grace Hopper\ntitle: Gewohnheit\nslug: gewohnheit\n+++\nText.");

        $this->assertSame('Gewohnheit', $post->getTitle());
        $this->assertSame('202611' . DIRECTORY_SEPARATOR . 'gewohnheit.html', $post->getFilename());
        $this->assertNull($post->getSourceUrl());
        $this->assertNull($post->getDescription());
    }

    public function testQuoteRequiresSource() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Quote post.md needs a "source: …"');

        $this->createPost("type: quote\ndate: 2026-11-02\n+++\nText.");
    }

    public function testRejectsUnknownType() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unknown type "video"');

        $this->createPost("type: video\ndate: 2026-11-02\n+++\nText.");
    }

    protected function createPost(string $content) : \Sprainocchio\Post\Post
    {
        file_put_contents($this->dir . '/post.md', $content);

        return (new PostCreator(new \Parsedown()))->fromFile(
            new SplFileInfo($this->dir . '/post.md', '', 'post.md')
        );
    }
}
