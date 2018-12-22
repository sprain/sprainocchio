<?php

namespace Sprainocchio\Archive;

use Twig_Environment;

class ArchiveSaver
{
    protected $twig;
    protected $targetDir;

    /** @var Archive */
    protected $archive;

    public function __construct(Twig_Environment $twig, string $targetDir)
    {
        $this->twig = $twig;
        $this->targetDir = $targetDir;
    }

    public function setArchive(Archive $archive) : self
    {
        $this->archive = $archive;

        return $this;
    }

    public function save() : void
    {
        $page = 1;
        $numberOfPages = $this->archive->getNumberOfPages();

        while ($page <= $numberOfPages) {
            $targetFile = $this->getTargetFile($page);
            $targetDir = dirname($targetFile);

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            file_put_contents($targetFile, $this->getHtml($page));
            $page++;
        }
    }

    protected function getTargetFile(int $page) : string
    {
        return $this->targetDir
            . DIRECTORY_SEPARATOR
            . ($this->archive->getDirName() ? $this->archive->getDirName() . DIRECTORY_SEPARATOR : null)
            . ($page == 1 ? 'index' : $page)
            . '.html';
    }

    protected function getHtml(int $page) : string
    {
        return $this->twig->render($this->archive->getTemplateName() . '.html.twig', [
            'page' => $page,
            'posts' => $this->archive->getPostsOfPage($page),
            'archive' => $this->archive
        ]);
    }
}