<?php

namespace Sprainocchio\Post;

use Twig_Environment;

class PostSaver
{
    protected $twig;
    protected $targetDir;

    /** @var Post */
    protected $post;

    public function __construct(Twig_Environment $twig, string $targetDir)
    {
        $this->twig = $twig;
        $this->targetDir = $targetDir;
    }

    public function setPost(Post $post) : self
    {
        $this->post = $post;

        return $this;
    }

    public function save() : string
    {
        $dir = dirname($this->getTargetFile());
        if(!is_dir($dir)) {
            mkdir($dir);
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