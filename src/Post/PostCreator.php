<?php

namespace Sprainocchio\Post;

use Dom\HTMLDocument;
use Sprainocchio\Util\Urlizer;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class PostCreator
{
    use Urlizer;

    protected ?string $rawContent = null;

    protected SplFileInfo $file;

    public function __construct(
        protected \Parsedown $mdParser
    ) {
    }

    // Pages (see Page\PageBuilder) have no URL date, so they don't need one
    public function fromFile(SplFileInfo $file, bool $requireDate = true) : Post
    {
        $this->file = $file;
        $this->rawContent = null;

        $type = $this->getType();
        $source = $this->getSource($type);

        return (new Post())
            ->setType($type)
            ->setSource($source)
            ->setSourceUrl($this->getStringMetaValue('source_url'))
            ->setDescription($this->getDescription())
            ->setDate($this->getDate($requireDate))
            ->setTitle($this->getTitle($type, $source))
            ->setSlug($this->getSlug($type, $source))
            ->setAuthor($this->getMetaValue('author'))
            ->setMeta($this->parseMetaContent())
            ->setSummary($this->getSummary())
            ->setImage($this->getImage())
            ->setTags($this->getTags())
            ->setContent($this->getHtml());
    }

    protected function getType() : string
    {
        $type = $this->getMetaValue('type') ?? Post::TYPE_ARTICLE;

        if (!in_array($type, Post::TYPES, true)) {
            throw new \RuntimeException(sprintf(
                'Post %s has the unknown type "%s"; known types are: %s',
                $this->file->getRelativePathname(),
                is_scalar($type) ? $type : get_debug_type($type),
                implode(', ', Post::TYPES)
            ));
        }

        return $type;
    }

    protected function getSource(string $type) : ?string
    {
        $source = $this->getStringMetaValue('source');

        if (Post::TYPE_QUOTE === $type && null === $source) {
            throw new \RuntimeException(sprintf(
                'Quote %s needs a "source: …" in its header',
                $this->file->getRelativePathname()
            ));
        }

        return $source;
    }

    // Short note in Markdown, rendered as HTML
    protected function getDescription() : ?string
    {
        $description = $this->getStringMetaValue('description');

        return null === $description ? null : $this->mdParser->text($description);
    }

    // Quotes usually have no heading, so their source serves as the title
    protected function getTitle(string $type = Post::TYPE_ARTICLE, ?string $source = null) : ?string
    {
        $title = $this->getMetaValue('title');

        if (null === $title && Post::TYPE_QUOTE === $type) {
            $title = $source;
        }

        if (null === $title) {
            $titles = $this->parseHtml()->getElementsByTagName('h1');
            if ($titles->length > 0) {
                $title = $titles->item(0)->textContent;
            }
        }

        return $title;
    }

    protected function getSummary() : ?string
    {
        $summary = $this->getMetaValue('summary');

        if (null == $summary) {
            $summaries = $this->parseHtml()->getElementsByTagName('p');
            if ($summaries->length > 0) {
                $summary = $summaries->item(0)->textContent;
            }
        }

        return $summary;
    }

    protected function getImage() : ?string
    {
        $image = $this->getMetaValue('image');

        if (null == $image) {
            $images = $this->parseHtml()->getElementsByTagName('img');
            if ($images->length > 0) {
                $image = $images->item(0)->getAttribute('src');
            }
        }

        return $image;
    }

    // Quotes without a slug: the source and the first words of the quote, e.g. grace-hopper-the-most-dangerous-phrase
    protected function getSlug(string $type = Post::TYPE_ARTICLE, ?string $source = null) : ?string
    {
        $slug = $this->getMetaValue('slug');

        if (null === $slug && Post::TYPE_QUOTE === $type) {
            $text = $this->parseHtml()->body?->textContent ?? '';
            $words = array_slice(preg_split('~\s+~u', trim($text), -1, PREG_SPLIT_NO_EMPTY), 0, 6);
            $slug = $this->urlize($source . ' ' . implode(' ', $words));
        }

        return $slug;
    }

    protected function getStringMetaValue(string $value) : ?string
    {
        $metaValue = $this->getMetaValue($value);

        if (null === $metaValue || '' === trim((string) $metaValue)) {
            return null;
        }

        return trim((string) $metaValue);
    }

    // Required, because the date is part of the post's URL and file dates change when files are copied
    protected function getDate(bool $required) : ?\DateTime
    {
        $unixTimestamp = $this->getMetaValue('date');

        if (null === $unixTimestamp && !$required) {
            return null;
        }

        if (!is_int($unixTimestamp)) {
            throw new \RuntimeException(sprintf(
                'Post %s needs a "date: YYYY-MM-DD" in its header',
                $this->file->getRelativePathname()
            ));
        }

        return (new \DateTime())->setTimestamp($unixTimestamp);
    }

    protected function getTags() : array
    {
        return $this->getMetaValue('tags') ?? [];
    }

    protected function parseHtml() : HTMLDocument
    {
        return HTMLDocument::createFromString(
            $this->getHtml(),
            LIBXML_NOERROR
        );
    }

    protected function getHtml() : string
    {
        $contentParts = $this->splitContent();

        return $this->mdParser->text(end($contentParts));
    }

    protected function getMetaValue(string $value) : mixed
    {
        $metaContent = $this->parseMetaContent();

        return $metaContent[$value] ?? null;
    }

    protected function parseMetaContent() : array
    {
        $metaContent = $this->getRawMetaContent();

        if (null === $metaContent) {
            return [];
        }

        return Yaml::parse($metaContent) ?? [];
    }

    protected function getRawMetaContent() : ?string
    {
        $contentParts = $this->splitContent();

        if (count($contentParts) > 1) {
            return $contentParts[0];
        }

        return null;
    }

    protected function splitContent() : array
    {
        return explode('+++', $this->getRawContent(), 2);
    }

    protected function getRawContent() : string
    {
        return $this->rawContent ??= file_get_contents($this->file->getRealPath());
    }
}
