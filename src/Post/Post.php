<?php

namespace Sprainocchio\Post;

use Sprainocchio\Util\Urlizer;

class Post
{
    use Urlizer;

    public const string TYPE_ARTICLE = 'article';

    public const string TYPE_QUOTE = 'quote';

    public const array TYPES = [self::TYPE_ARTICLE, self::TYPE_QUOTE];

    protected ?\DateTime $date = null;

    protected ?string $title = null;

    protected ?string $slug = null;

    protected ?string $author = null;

    // Every field of the header, including ones the engine doesn't know, for themes
    protected array $meta = [];

    protected ?string $summary = null;

    protected ?string $image = null;

    protected array $tags = [];

    protected ?string $content = null;

    protected string $type = self::TYPE_ARTICLE;

    // Quotes: who said or wrote it, where it was published and an optional note (HTML)
    protected ?string $source = null;

    protected ?string $sourceUrl = null;

    protected ?string $description = null;

    public function getDate() : ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date) : self
    {
        $this->date = $date;

        return $this;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title) : self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug() : ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug) : self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMeta() : array
    {
        return $this->meta;
    }

    public function setMeta(array $meta) : self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getAuthor() : ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author) : self
    {
        $this->author = $author;

        return $this;
    }

    public function getSummary() : ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary) : self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getImage() : ?string
    {
        return $this->image;
    }

    public function setImage(?string $image) : self
    {
        $this->image = $image;

        return $this;
    }

    public function getTags() : array
    {
        return $this->tags;
    }

    public function setTags(array $tags) : self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }

    public function setContent(string $content) : self
    {
        $this->content = $content;

        return $this;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    public function getSource() : ?string
    {
        return $this->source;
    }

    public function setSource(?string $source) : self
    {
        $this->source = $source;

        return $this;
    }

    public function getSourceUrl() : ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl) : self
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    public function getFilename() : string
    {
        return $this->getDate()->format('Ym')
            . DIRECTORY_SEPARATOR
            . ($this->getSlug() ?? $this->urlize($this->getTitle() ?? ''))
            . '.html';
    }
}
