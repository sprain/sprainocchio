<?php

namespace Sprainocchio\Post;

use Masterminds\HTML5;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class PostCreator
{
    protected $mdParser;

    protected $htmlParser;

    protected $rawContent;

    /** @type SplFileInfo */
    protected $file;

    public function __construct(\Parsedown $mdParser, HTML5 $htmlParser)
    {
        $this->mdParser = $mdParser;
        $this->htmlParser = $htmlParser;
    }

    public function fromFile(SplFileInfo $file) : Post
    {
        $this->file = $file;
        $this->rawContent = null;

        return (new Post())
            ->setDate($this->getDate())
            ->setTitle($this->getTitle())
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

    protected function getDate() : \DateTime
    {
        $unixTimestamp = $this->getMetaValue('date');

        if (null === $unixTimestamp) {
            $unixTimestamp = $this->file->getCTime();
        }

        return (new \DateTime())->setTimestamp($unixTimestamp);
    }

    protected function getTags() : array
    {
        $tags = $this->getMetaValue('tags');

        if (null === $tags) {
            $tags = [];
        }

        return $tags;
    }

    protected function parseHtml() : \DOMDocument
    {
        return $this->htmlParser->parse(
            $this->getHtml()
        );
    }

    protected function getHtml() : string
    {
        $content = $this->getRawContent();
        $contentParts = explode('+++', $content);

        $md = $contentParts[0];
        if (count($contentParts) > 1) {
            $md = $contentParts[1];
        }

        return $this->mdParser->text($md);
    }

    protected function getMetaValue(string $value)
    {
        $metaContent = $this->parseMetaContent();

        if (isset($metaContent[$value])) {
            return $metaContent[$value];
        }

        return null;
    }

    protected function parseMetaContent()
    {
        $metaContent = $this->getRawMetaContent();

        return Yaml::parse($metaContent);
    }

    protected function getRawMetaContent() : string
    {
        $content = $this->getRawContent();
        $contentParts = explode('+++', $content);

        if (count($contentParts) > 0) {
            return $contentParts[0];
        }

        return null;
    }

    protected function getRawContent() : string
    {
        if (null !== $this->rawContent) {
            return $this->rawContent;
        }

        return file_get_contents($this->file->getRealPath());
    }
}