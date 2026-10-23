<?php

namespace Sprainocchio\Post;

use Twig\Environment;

class PostSaver
{
    protected Post $post;

    public function __construct(
        protected Environment $twig,
        protected string $targetDir
    ) {
    }

    public function setPost(Post $post) : self
    {
        $this->post = $post;

        return $this;
    }

    public function save() : string
    {
        $dir = dirname($this->getTargetFile());
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->getTargetFile(), $this->getHtml());

        return $this->getTargetFile();
    }

    protected function getTargetFile() : string
    {
        return $this->targetDir
            . DIRECTORY_SEPARATOR
            . $this->post->getFilename();
    }

    protected function getHtml() : string
    {
        return $this->twig->render('single.html.twig', [
            'post' => $this->post
        ]);
    }
}
