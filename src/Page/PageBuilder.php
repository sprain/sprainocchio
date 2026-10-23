<?php

namespace Sprainocchio\Page;

use Sprainocchio\Post\PostCreator;
use Sprainocchio\Post\PostFinder;
use Sprainocchio\Util\Urlizer;
use Twig\Environment;

// Pages are Markdown files in pages/ that live outside the post lists, e.g. an about page.
// They are saved as <slug>.html at the top of the public folder (template page.html.twig).
class PageBuilder
{
    use Urlizer;

    public function __construct(
        protected PostFinder $pageFinder,
        protected PostCreator $postCreator,
        protected Environment $twig,
        protected string $targetDir
    ) {
    }

    public function buildPages() : void
    {
        $sourceFiles = [];

        foreach ($this->pageFinder->getPostFiles() as $file) {
            $page = $this->postCreator->fromFile($file, false);
            $filename = ($page->getSlug() ?? $this->urlize($page->getTitle() ?? '')) . '.html';

            if (in_array($filename, ['.html', 'index.html'], true) || isset($sourceFiles[$filename])) {
                throw new \RuntimeException(sprintf(
                    'Page %s cannot be saved as %s',
                    $file->getRelativePathname(),
                    $filename
                ));
            }
            $sourceFiles[$filename] = $file->getRelativePathname();

            file_put_contents(
                $this->targetDir . DIRECTORY_SEPARATOR . $filename,
                $this->twig->render('page.html.twig', ['page' => $page])
            );
        }
    }
}
