<?php

namespace Sprainocchio\Post;

use Sprainocchio\Util\Urlizer;

class Post
{
    use Urlizer;

    protected ?\DateTime $date = null;

    protected ?string $title = null;

    protected ?string $slug = null;

    protected ?string $summary = null;

    protected ?string $image = null;

    protected array $tags = [];

    protected ?string $content = null;

    public function getDate() : ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date) : self
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

    public function getFilename() : string
    {
        return $this->getDate()->format('Ym')
            . DIRECTORY_SEPARATOR
            . ($this->getSlug() ?? $this->urlize($this->getTitle() ?? ''))
            . '.html';
    }
}
