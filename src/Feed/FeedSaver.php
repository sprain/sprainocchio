<?php

namespace Sprainocchio\Feed;

use Sprainocchio\Post\Post;

// Writes an Atom feed of the newest posts, so readers can follow the blog in a feed reader
class FeedSaver
{
    public const FILENAME = 'feed.xml';

    public function __construct(
        protected string $targetDir,
        protected string $baseUrl,
        protected string $title,
        protected ?string $author = null,
        protected int $limit = 20
    ) {
    }

    /**
     * @param Post[] $posts newest first
     */
    public function save(array $posts) : void
    {
        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0777, true);
        }

        file_put_contents(
            $this->targetDir . DIRECTORY_SEPARATOR . self::FILENAME,
            $this->getXml(array_slice($posts, 0, $this->limit))
        );
    }

    /**
     * @param Post[] $posts
     */
    protected function getXml(array $posts) : string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $feed = $doc->appendChild($doc->createElementNS('http://www.w3.org/2005/Atom', 'feed'));
        // Relative links and images in post content resolve against the blog, as they do on its pages
        $feed->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:base', $this->baseUrl);

        $this->addText($feed, 'id', $this->baseUrl);
        $this->addText($feed, 'title', $this->title);
        $this->addText($feed, 'updated', ($posts ? $posts[0]->getDate() : new \DateTime())->format(DATE_ATOM));
        $this->addLink($feed, $this->baseUrl, 'alternate', 'text/html');
        $this->addLink($feed, $this->baseUrl . self::FILENAME, 'self', 'application/atom+xml');
        if (null !== $this->author) {
            $this->addText($this->addElement($feed, 'author'), 'name', $this->author);
        }

        foreach ($posts as $post) {
            $url = $this->baseUrl . str_replace(DIRECTORY_SEPARATOR, '/', $post->getFilename());

            $entry = $this->addElement($feed, 'entry');
            $this->addText($entry, 'id', $url);
            $this->addText($entry, 'title', $post->getTitle() ?? '');
            $this->addLink($entry, $url, 'alternate', 'text/html');
            $this->addText($entry, 'published', $post->getDate()->format(DATE_ATOM));
            $this->addText($entry, 'updated', $post->getDate()->format(DATE_ATOM));

            // Atom requires an author for every entry unless the feed has one
            $author = $post->getAuthor() ?? $this->author;
            if (null !== $author) {
                $this->addText($this->addElement($entry, 'author'), 'name', $author);
            }

            // Co-written posts name their second author in the header field coauthor
            $coauthor = $post->getMeta()['coauthor'] ?? null;
            if (is_string($coauthor) && '' !== $coauthor) {
                $this->addText($this->addElement($entry, 'contributor'), 'name', $coauthor);
            }

            foreach ($post->getTags() as $tag) {
                $this->addElement($entry, 'category')->setAttribute('term', $tag);
            }

            if (null !== $post->getSummary()) {
                $this->addText($entry, 'summary', $post->getSummary());
            }

            // Feed readers show the title themselves, so a leading <h1> would appear twice
            $content = preg_replace('~^\s*<h1>.*?</h1>\s*~s', '', $post->getContent() ?? '', 1);
            $this->addText($entry, 'content', $content)->setAttribute('type', 'html');
        }

        return $doc->saveXML();
    }

    protected function addElement(\DOMElement $parent, string $name) : \DOMElement
    {
        return $parent->appendChild($parent->ownerDocument->createElementNS('http://www.w3.org/2005/Atom', $name));
    }

    protected function addText(\DOMElement $parent, string $name, string $text) : \DOMElement
    {
        $element = $this->addElement($parent, $name);
        $element->appendChild($parent->ownerDocument->createTextNode($text));

        return $element;
    }

    protected function addLink(\DOMElement $parent, string $href, string $rel, string $type) : void
    {
        $link = $this->addElement($parent, 'link');
        $link->setAttribute('rel', $rel);
        $link->setAttribute('type', $type);
        $link->setAttribute('href', $href);
    }
}
