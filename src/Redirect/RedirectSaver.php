<?php

namespace Sprainocchio\Redirect;

// Keeps old URLs working on any static host by writing a small HTML page that forwards to the new URL
class RedirectSaver
{
    public function __construct(
        protected string $targetDir,
        protected string $baseUrl
    ) {
    }

    /**
     * @param array<string, string> $redirects old path => new path, both relative to baseUrl
     */
    public function save(array $redirects) : void
    {
        foreach ($redirects as $from => $to) {
            $targetFile = $this->targetDir . DIRECTORY_SEPARATOR . ltrim($from, '/');

            if (!is_dir(dirname($targetFile))) {
                mkdir(dirname($targetFile), 0777, true);
            }

            file_put_contents($targetFile, $this->getHtml($this->baseUrl . ltrim($to, '/')));
        }
    }

    protected function getHtml(string $url) : string
    {
        $url = htmlspecialchars($url);

        return <<<HTML
            <!doctype html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Weitergeleitet</title>
                <link rel="canonical" href="{$url}">
                <meta http-equiv="refresh" content="0; url={$url}">
            </head>
            <body>
                <p><a href="{$url}">{$url}</a></p>
            </body>
            </html>

            HTML;
    }
}
