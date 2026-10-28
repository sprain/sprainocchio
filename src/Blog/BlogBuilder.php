<?php

namespace Sprainocchio\Blog;

use Sprainocchio\Archive\Archive;
use Sprainocchio\Archive\ArchiveSaver;
use Sprainocchio\Feed\FeedSaver;
use Sprainocchio\Post\Post;
use Sprainocchio\Post\PostCreator;
use Sprainocchio\Post\PostFinder;
use Sprainocchio\Post\PostSaver;

class BlogBuilder
{
    public function __construct(
        protected PostFinder $postFinder,
        protected PostCreator $postCreator,
        protected PostSaver $postSaver,
        protected ArchiveSaver $archiveSaver,
        protected Archive $mainArchive,
        protected Archive $tagArchive,
        protected Archive $startpage,
        protected ?FeedSaver $feedSaver = null
    ) {
    }

    public function buildBlog() : void
    {
        $posts = $this->getPosts();

        $this->buildPosts($posts);
        $this->buildMainArchive($posts);
        $this->buildTagArchives($posts);
        $this->buildStartpage($posts);
        $this->feedSaver?->save($posts);
    }

    protected function buildPosts(array $posts) : void
    {
        foreach ($posts as $post) {
            $this->postSaver->setPost($post)->save();
        }
    }

    protected function buildStartpage(array $posts) : void
    {
        $this->startpage->setPosts($posts);

        $this->archiveSaver
            ->setArchive($this->startpage)
            ->save();
    }

    protected function buildMainArchive(array $posts) : void
    {
        $this->mainArchive
            ->setPosts($posts);

        $this->archiveSaver
            ->setArchive($this->mainArchive)
            ->save();
    }

    protected function buildTagArchives(array $posts) : void
    {
        $tagArchives = [];

        foreach ($posts as $post) {
            $tags = $post->getTags();
            foreach ($tags as $tag) {
                $tagArchives[$tag][] = $post;
            }
        }

        foreach ($tagArchives as $tag => $posts) {
            $this->buildTagArchive($tag, $posts);
        }
    }

    protected function buildTagArchive(string $tag, array $posts) : void
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

        $sourceFiles = [];

        foreach ($this->postFinder->getPostFiles() as $file) {
            $post = $this->postCreator->fromFile($file);

            $filename = $post->getFilename();
            if (isset($sourceFiles[$filename])) {
                throw new \RuntimeException(sprintf(
                    'Posts %s and %s would both be saved as %s',
                    $sourceFiles[$filename],
                    $file->getRelativePathname(),
                    $filename
                ));
            }
            $sourceFiles[$filename] = $file->getRelativePathname();

            $posts[] = $post;
        }

        usort($posts, fn(Post $a, Post $b) => $b->getDate() <=> $a->getDate());

        return $posts;
    }
}
