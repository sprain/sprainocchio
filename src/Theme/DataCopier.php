<?php

namespace Sprainocchio\Theme;

use Symfony\Component\Yaml\Yaml;

class DataCopier
{
    protected array $themeConfig;

    public function __construct(
        protected string $themePath,
        protected string $publicPath
    ) {
        $this->themeConfig = $this->parseBuildConfig();
    }

    public function copyPublicData() : void
    {
        foreach ($this->themeConfig['public_dirs'] ?? [] as $dir) {
            $this->rcopy(
                $this->themePath . DIRECTORY_SEPARATOR . $dir,
                $this->publicPath . DIRECTORY_SEPARATOR . $dir
            );
        }
    }

    // Copies the project's own files (e.g. images used in posts) into the public folder as they are
    public function copyStaticData(string $staticPath) : void
    {
        if (is_dir($staticPath)) {
            $this->rcopy($staticPath, $this->publicPath);
        }
    }

    protected function parseBuildConfig() : array
    {
        $configFile = $this->themePath . DIRECTORY_SEPARATOR . 'build.yml';
        if (file_exists($configFile)) {
            return Yaml::parse(file_get_contents($configFile));
        }

        return [];
    }

    protected function rcopy(string $src, string $dest) : void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0777, true);
        }

        $i = new \DirectoryIterator($src);
        foreach ($i as $f) {
            if ($f->isFile()) {
                copy($f->getRealPath(), "$dest/" . $f->getFilename());
            } else if (!$f->isDot() && $f->isDir()) {
                $this->rcopy($f->getRealPath(), "$dest/$f");
            }
        }
    }
}
