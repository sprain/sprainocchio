<?php

namespace Sprainocchio\Post;

use Dom\HTMLDocument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class PostCreator
{
    protected ?string $rawContent = null;

    protected SplFileInfo $file;

    public function __construct(
        protected \Parsedown $mdParser
    ) {
    }

    public function fromFile(SplFileInfo $file) : Post
    {
        $this->file = $file;
        $this->rawContent = null;

        return (new Post())
            ->setDate($this->getDate())
            ->setTitle($this->getTitle())
            ->setSlug($this->getSlug())
            ->setSummary($this->getSummary())
            ->setImage($this->getImage())
            ->setTags($this->getTags())
            ->setContent($this->getHtml());
    }

    protected function getTitle() : ?string
    {
        $title = $this->getMetaValue('title');

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

    protected function getSlug() : ?string
    {
        return $this->getMetaValue('slug');
    }

    // Required, because the date is part of the post's URL and file dates change when files are copied
    protected function getDate() : \DateTime
    {
        $unixTimestamp = $this->getMetaValue('date');

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
