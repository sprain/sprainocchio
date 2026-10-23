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

    public function getPostFiles() : iterable
    {
        if (!is_dir($this->dir)) {
            return [];
        }

        return $this->finder
            ->files()
            ->in($this->dir)
            ->name('*.md');
    }
}
