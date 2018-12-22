<?php

namespace Sprainocchio\Archive;

class Archive
{
    protected $name;
    protected $posts;
    protected $postsPerPage;
    protected $path;
    protected $templateName;

    public function __construct(int $postsPerPage = 20)
    {
        $this->posts = [];
        $this->postsPerPage = $postsPerPage;
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

    public function getPosts()
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

    public function setPath($path) : self
    {
        $this->path = $path;

        return $this;
    }

    public function getTemplateName() : ?string
    {
        return $this->templateName;
    }

    public function setTemplateName($templateName) : self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getPostsPerPage(): int
    {
        return $this->postsPerPage;
    }

    public function getPostsOfPage(int $pageNumber) : array
    {
        return array_slice($this->posts, (($pageNumber-1) * $this->postsPerPage), $this->postsPerPage);
    }

    public function getNumberOfPages() : int
    {
        return ceil(count($this->posts) / $this->postsPerPage);
    }

    public function getDirName() : string
    {
        $dirName = '';
        if ($this->getPath()) {
            $dirName = $this->getPath() . DIRECTORY_SEPARATOR;
        }

        $dirName .= $this->urlize($this->getName());

        return $dirName;
    }

    public function urlize($string) {
        $string = preg_replace('~[^\\pL0-9_]+~u', '-', $string);
        $string = trim($string, "-");
        $string = iconv("utf-8", "us-ascii//TRANSLIT", $string);
        $string = strtolower($string);
        $string = preg_replace('~[^-a-z0-9_]+~', '', $string);

        return $string;
    }
}