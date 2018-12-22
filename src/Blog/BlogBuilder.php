<?php

namespace Sprainocchio\Blog;

use Sprainocchio\Archive\Archive;
use Sprainocchio\Archive\ArchiveSaver;
use Sprainocchio\Post\PostCreator;
use Sprainocchio\Post\PostFinder;
use Sprainocchio\Post\PostSaver;

class BlogBuilder
{
    protected $postFinder;
    protected $postCreator;
    protected $postSaver;
    protected $archiveSaver;
    protected $mainArchive;
    protected $tagArchive;
    protected $startpage;

    public function __construct(
        PostFinder $postFinder,
        PostCreator $postCreator,
        PostSaver $postSaver,
        ArchiveSaver $archiveSaver,
        Archive $mainArchive,
        Archive $tagArchive,
        Archive $startpage
    )
    {
        $this->postFinder = $postFinder;
        $this->postCreator = $postCreator;
        $this->postSaver = $postSaver;
        $this->archiveSaver = $archiveSaver;
        $this->mainArchive = $mainArchive;
        $this->tagArchive = $tagArchive;
        $this->startpage = $startpage;
    }

    public function buildBlog()
    {
        $posts = $this->getPosts();

        $this->buildPosts($posts);
        $this->buildMainArchive($posts);
        $this->buildTagArchives($posts);
        $this->buildStartpage($posts);
    }

    protected function buildPosts(array $posts)
    {
        foreach ($posts as $post) {
            $this->postSaver->setPost($post)->save();
        }
    }

    protected function buildStartpage(array $posts)
    {
        $this->startpage->setPosts($posts);

        $this->archiveSaver
            ->setArchive($this->startpage)
            ->save();
    }

    protected function buildMainArchive(array $posts)
    {
        $this->mainArchive
            ->setPosts($posts);

        $this->archiveSaver
            ->setArchive($this->mainArchive)
            ->save();
    }

    protected function buildTagArchives(array $posts)
    {
        $tagArchives = [];

        foreach ($posts as $post) {
            $tags = $post->getTags();
            foreach($tags as $tag) {
                $tagArchives[$tag][] = $post;
            }
        }

        foreach($tagArchives as $tag => $posts) {
            $this->buildTagArchive($tag, $posts);
        }

    }

    protected function buildTagArchive(string $tag, array $posts)
    {
        $this->tagArchive
            ->setPosts($posts)
            ->setName($tag);

        $this->archiveSaver
            ->setArchive($this->tagArchive)
            ->save();
    }

    protected function getPosts() : array
    {
        $posts = [];

        foreach($this->postFinder->getPostFiles() as $file) {
            $post = $this->postCreator->fromFile($file);
            $posts[] = $post;
        }

        usort($posts, function($a, $b){
            return $a->getDate() < $b->getDate();
        });

        return $posts;
    }
}