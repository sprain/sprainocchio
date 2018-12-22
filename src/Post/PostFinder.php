<?php

namespace Sprainocchio\Post;

use Symfony\Component\Finder\Finder;

class PostFinder
{
    protected $finder;
    protected $dir;

    public function __construct(Finder $finder, string $dir)
    {
        $this->finder = $finder;
        $this->dir = $dir;
    }

    public function getPostFiles() : Finder
    {
        return $this->finder
            ->files()
            ->in($this->dir)
            ->name('*.md');
    }
}