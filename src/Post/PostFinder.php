<?php

namespace Sprainocchio\Post;

use Symfony\Component\Finder\Finder;

class PostFinder
{
    public function __construct(
        protected Finder $finder,
        protected string $dir
    ) {
    }

    public function getPostFiles() : Finder
    {
        return $this->finder
            ->files()
            ->in($this->dir)
            ->name('*.md');
    }
}
