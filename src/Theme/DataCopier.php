<?php

namespace Sprainocchio\Theme;

class DataCopier
{
    protected $themePath;
    protected $publicPath;

    public function __construct(string $themePath, string $publicPath)
    {
        $this->themePath = $themePath;
        $this->publicPath = $publicPath;
    }

    public function copyPublicData()
    {
        $this->rcopy(
            $this->themePath . DIRECTORY_SEPARATOR . 'public',
            $this->publicPath
        );
    }

    protected function rcopy($src, $dest)
    {
        if(!is_dir($dest)) {
            mkdir($dest);
        }

        $i = new \DirectoryIterator($src);
        foreach($i as $f) {
            if($f->isFile()) {
                copy($f->getRealPath(), "$dest/" . $f->getFilename());
            } else if(!$f->isDot() && $f->isDir()) {
                $this->rcopy($f->getRealPath(), "$dest/$f");
            }
        }
    }
}