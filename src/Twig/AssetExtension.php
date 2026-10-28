<?php

namespace Sprainocchio\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

// asset('styles/styles.css') → https://example.com/blog/styles/styles.css?v=3f2a9c1e
// The version is a hash of the file's content, so browsers load a file again only after it changed.
class AssetExtension extends AbstractExtension
{
    protected array $versions = [];

    public function __construct(
        protected string $publicPath,
        protected string $baseUrl
    ) {
    }

    public function getFunctions() : array
    {
        return [
            new TwigFunction('asset', $this->asset(...)),
        ];
    }

    public function asset(string $path) : string
    {
        $path = ltrim($path, '/');

        return $this->baseUrl . $path . '?v=' . ($this->versions[$path] ??= $this->getVersion($path));
    }

    protected function getVersion(string $path) : string
    {
        // Theme and static files are copied into public/ before any template is rendered
        $file = rtrim($this->publicPath, '/') . '/' . $path;
        if (!is_file($file)) {
            throw new \RuntimeException(sprintf(
                'asset(\'%s\'): %s does not exist. Is the file in one of the theme\'s public_dirs or in static/?',
                $path,
                $file
            ));
        }

        return substr(hash_file('xxh3', $file), 0, 8);
    }
}
