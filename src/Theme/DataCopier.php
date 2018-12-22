<?php

namespace Sprainocchio\Theme;

use Symfony\Component\Yaml\Yaml;

class DataCopier
{
    protected $themePath;
    protected $publicPath;
    protected $themeConfig;

    public function __construct(string $themePath, string $publicPath)
    {
        $this->themePath = $themePath;
        $this->publicPath = $publicPath;
        $this->themeConfig = $this->parseBuildConfig();
    }

    public function copyPublicData()
    {
        foreach($this->themeConfig['public_dirs'] as $dir) {
            $this->rcopy(
                $this->themePath . DIRECTORY_SEPARATOR . $dir,
                $this->publicPath . DIRECTORY_SEPARATOR . $dir
            );
        }
    }

    protected function parseBuildConfig() : array
    {
        $configFile = $this->themePath . DIRECTORY_SEPARATOR . 'build.yml';
        if (file_exists($configFile)){
            return Yaml::parse(file_get_contents($configFile));
        }

        return [];
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