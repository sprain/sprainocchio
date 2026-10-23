<?php

namespace Sprainocchio\Archive;

use Sprainocchio\Util\Urlizer;

class Archive
{
    use Urlizer;

    protected ?string $name = null;
    protected array $posts = [];
    protected ?string $path = null;
    protected ?string $templateName = null;

    public function __construct(
        protected int $postsPerPage = 20
    ) {
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getPosts() : array
    {
        return $this->posts;
    }

    public function setPosts(array $posts) : self
    {
        $this->posts = $posts;

        return $this;
    }

    public function getPath() : ?string
    {
        return $this->path;
    }

    public function setPath(?string $path) : self
    {
        $this->path = $path;

        return $this;
    }

    public function getTemplateName() : ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName) : self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getPostsPerPage() : int
    {
        return $this->postsPerPage;
    }

    public function getPostsOfPage(int $pageNumber) : array
    {
        return array_slice($this->posts, (($pageNumber-1) * $this->postsPerPage), $this->postsPerPage);
    }

    public function getNumberOfPages() : int
    {
        return (int) ceil(count($this->posts) / $this->postsPerPage);
    }

    public function getDirName() : string
    {
        $dirName = '';
        if ($this->getPath()) {
            $dirName = $this->getPath() . DIRECTORY_SEPARATOR;
        }

        $dirName .= $this->urlize($this->getName() ?? '');

        return $dirName;
    }
}
