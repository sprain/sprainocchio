<?php

namespace Sprainocchio\Tests\Feed;

use PHPUnit\Framework\TestCase;
use Sprainocchio\Feed\FeedSaver;
use Sprainocchio\Post\Post;

class FeedSaverTest extends TestCase
{
    protected string $dir;

    protected function setUp() : void
    {
        $this->dir = sys_get_temp_dir() . '/sprainocchio-feed-' . uniqid();
    }

    protected function tearDown() : void
    {
        @unlink($this->dir . '/feed.xml');
        @rmdir($this->dir);
    }

    public function testWritesAtomFeed() : void
    {
        (new FeedSaver($this->dir, 'https://example.com/blog/', 'Mein Blog', 'Manuel'))->save([
            $this->post('2026-10-23', 'Ein Blog & ein Experiment', 'Claude (KI)', ['Internet']),
            $this->post('2019-06-17', 'Faire Preise', null, [])->setMeta(['coauthor' => 'Claude (KI)']),
        ]);

        $xml = simplexml_load_file($this->dir . '/feed.xml');
        $xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');

        $this->assertSame('Mein Blog', (string) $xml->title);
        $this->assertSame('https://example.com/blog/', (string) $xml->id);
        $this->assertSame('2026-10-23T00:00:00+00:00', (string) $xml->updated);
        $this->assertSame('https://example.com/blog/feed.xml', (string) $xml->xpath('a:link[@rel="self"]')[0]['href']);
        $this->assertCount(2, $xml->entry);

        $first = $xml->entry[0];
        $this->assertSame('Ein Blog & ein Experiment', (string) $first->title);
        $this->assertSame('https://example.com/blog/202610/ein-blog-ein-experiment.html', (string) $first->id);
        $this->assertSame('Claude (KI)', (string) $first->author->name);
        $this->assertSame('Internet', (string) $first->category['term']);
        $this->assertSame('html', (string) $first->content['type']);
        $this->assertSame('<p>Text von Ein Blog & ein Experiment</p>', (string) $first->content);

        // Without its own author, an entry gets the feed's
        $this->assertSame('Manuel', (string) $xml->entry[1]->author->name);
        $this->assertSame('Claude (KI)', (string) $xml->entry[1]->contributor->name);
        $this->assertCount(0, $first->contributor);
    }

    public function testKeepsOnlyNewestPosts() : void
    {
        $posts = [];
        for ($day = 25; $day > 0; $day--) {
            $posts[] = $this->post(sprintf('2026-01-%02d', $day), 'Post ' . $day, null, []);
        }

        (new FeedSaver($this->dir, 'https://example.com/', 'Blog', null, 20))->save($posts);

        $xml = simplexml_load_file($this->dir . '/feed.xml');
        $this->assertCount(20, $xml->entry);
        $this->assertSame('Post 25', (string) $xml->entry[0]->title);
    }

    protected function post(string $date, string $title, ?string $author, array $tags) : Post
    {
        return (new Post())
            ->setDate(new \DateTime($date, new \DateTimeZone('UTC')))
            ->setTitle($title)
            ->setAuthor($author)
            ->setTags($tags)
            ->setSummary('Text von ' . $title)
            ->setContent("<h1>$title</h1>\n<p>Text von $title</p>");
    }
}
