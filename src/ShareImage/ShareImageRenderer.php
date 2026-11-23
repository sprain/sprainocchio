<?php

namespace Sprainocchio\ShareImage;

use Sprainocchio\Post\Post;
use Twig\Environment;

// Draws a 1200 × 630 PNG for link previews (og:image) of a post: an optional label at the top,
// the title in large type above a meta line at the bottom. Fonts, colours, background and the
// texts (Twig templates) come from the theme's config.yml, section share_images. Needs GD with FreeType.
class ShareImageRenderer
{
    public const int WIDTH = 1200;

    public const int HEIGHT = 630;

    public const string DIR = 'share';

    protected const array DEFAULTS = [
        'font' => null,
        'meta_font' => null,
        'label_font' => null,
        'background' => '#ffffff',
        'color' => '#111111',
        'meta_color' => null,
        'label_color' => null,
        'padding' => 80,
        'title_size' => 76,
        'title_min_size' => 44,
        'line_height' => 1.12,
        'meta_size' => 26,
        'label_size' => 26,
        'title' => '{{ post.title }}',
        'meta' => "{{ post.date|date('Y-m-d') }}",
        'label' => '',
    ];

    protected array $config;

    protected ?\GdImage $background = null;

    public function __construct(
        protected Environment $twig,
        protected string $themePath,
        protected string $publicPath,
        array $config
    ) {
        if (!function_exists('imagettftext')) {
            throw new \RuntimeException('share_images in the theme\'s config.yml needs PHP\'s GD extension with FreeType support.');
        }

        $config = array_merge(self::DEFAULTS, $config);
        if (null === $config['font']) {
            throw new \RuntimeException('share_images in the theme\'s config.yml needs a font (path to a .ttf or .otf file in the theme).');
        }
        $config['meta_font'] ??= $config['font'];
        $config['label_font'] ??= $config['meta_font'];
        $config['meta_color'] ??= $config['color'];
        $config['label_color'] ??= $config['meta_color'];

        foreach (['font', 'meta_font', 'label_font'] as $key) {
            $config[$key] = $this->themeFile($config[$key], $key);
        }

        $this->config = $config;
    }

    // Path of the image relative to the public folder, e.g. share/201906/faire-preise.png
    public static function getPath(Post $post) : string
    {
        return self::DIR . '/' . preg_replace('~\.html$~', '.png', str_replace(DIRECTORY_SEPARATOR, '/', $post->getFilename()));
    }

    // Draws the image into the public folder and returns its path relative to it
    public function render(Post $post) : string
    {
        $path = self::getPath($post);
        $file = rtrim($this->publicPath, '/') . '/' . $path;
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        $image = $this->createCanvas();
        $c = $this->config;
        $width = self::WIDTH - 2 * $c['padding'];

        $label = $this->text($c['label'], $post);
        $meta = $this->text($c['meta'], $post);
        $title = $this->text($c['title'], $post);

        $top = $c['padding'];
        if ('' !== $label) {
            $label = $this->fit($label, $c['label_font'], $c['label_size'], $width);
            $this->draw($image, $label, $c['label_font'], $c['label_size'], $c['label_color'], $top + $c['label_size']);
            $top += $c['label_size'] * 2.2;
        }

        $bottom = self::HEIGHT - $c['padding'];
        if ('' !== $meta) {
            $meta = $this->fit($meta, $c['meta_font'], $c['meta_size'], $width);
            $this->draw($image, $meta, $c['meta_font'], $c['meta_size'], $c['meta_color'], $bottom);
            $bottom -= $c['meta_size'] * 2.4;
        }

        // The largest size at which the title fits between label and meta line; at the smallest, it is cut off with …
        $available = $bottom - $top;
        $size = $c['title_size'];
        while (true) {
            $lines = $this->wrap($title, $c['font'], $size, $width);
            if (count($lines) * $size * $c['line_height'] <= $available || $size <= $c['title_min_size']) {
                break;
            }
            $size = max($c['title_min_size'], $size - 2);
        }
        $maxLines = max(1, (int) floor($available / ($size * $c['line_height'])));
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = $this->fit($lines[$maxLines - 1] . ' …', $c['font'], $size, $width);
        }

        // Bottom-aligned: the last line sits right above the meta line
        $lineHeight = $size * $c['line_height'];
        $baseline = $bottom - (count($lines) - 1) * $lineHeight;
        foreach ($lines as $line) {
            $this->draw($image, $line, $c['font'], $size, $c['color'], (int) round($baseline));
            $baseline += $lineHeight;
        }

        imagepng($image, $file, 9);

        return $path;
    }

    protected function createCanvas() : \GdImage
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $background = $this->config['background'];

        if (preg_match('~^#[0-9a-f]{3}([0-9a-f]{3})?$~i', $background)) {
            imagefill($image, 0, 0, $this->color($image, $background));

            return $image;
        }

        if (null === $this->background) {
            $source = imagecreatefromstring(file_get_contents($this->themeFile($background, 'background')));
            if (false === $source) {
                throw new \RuntimeException(sprintf('share_images: the background %s is not an image GD can read.', $background));
            }
            // Scaled to cover the canvas, centred
            $scale = max(self::WIDTH / imagesx($source), self::HEIGHT / imagesy($source));
            $w = (int) ceil(self::WIDTH / $scale);
            $h = (int) ceil(self::HEIGHT / $scale);
            $this->background = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
            imagecopyresampled(
                $this->background, $source, 0, 0,
                (int) ((imagesx($source) - $w) / 2), (int) ((imagesy($source) - $h) / 2),
                self::WIDTH, self::HEIGHT, $w, $h
            );
        }
        imagecopy($image, $this->background, 0, 0, 0, 0, self::WIDTH, self::HEIGHT);

        return $image;
    }

    // Renders a Twig template from the config as plain text on one line
    protected function text(string $template, Post $post) : string
    {
        $html = $this->twig->createTemplate($template)->render(['post' => $post]);

        return trim(preg_replace('~\s+~u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    protected function wrap(string $text, string $font, int $size, int $width) : array
    {
        $lines = [];
        $line = '';
        foreach (explode(' ', $text) as $word) {
            $candidate = '' === $line ? $word : $line . ' ' . $word;
            if ('' !== $line && $this->width($candidate, $font, $size) > $width) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        $lines[] = $line;

        return $lines;
    }

    // Shortens a line with … until it fits
    protected function fit(string $text, string $font, int $size, int $width) : string
    {
        if ($this->width($text, $font, $size) <= $width) {
            return $text;
        }
        $text = rtrim($text, ' …');
        while (mb_strlen($text) > 1 && $this->width($text . ' …', $font, $size) > $width) {
            $text = rtrim(mb_substr($text, 0, -1));
        }

        return $text . ' …';
    }

    // Sizes are in pixels; GD expects points at 96 dpi
    protected function width(string $text, string $font, int $size) : int
    {
        $box = imagettfbbox($size * 0.75, 0, $font, $text);

        return $box[2] - $box[0];
    }

    protected function draw(\GdImage $image, string $text, string $font, int $size, string $color, int $baseline) : void
    {
        imagettftext($image, $size * 0.75, 0, $this->config['padding'], $baseline, $this->color($image, $color), $font, $text);
    }

    protected function color(\GdImage $image, string $hex) : int
    {
        $hex = ltrim($hex, '#');
        if (3 === strlen($hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('~^[0-9a-f]{6}$~i', $hex)) {
            throw new \RuntimeException(sprintf('share_images: %s is not a colour like #1a2b3c.', $hex));
        }

        return imagecolorallocate($image, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }

    protected function themeFile(string $path, string $key) : string
    {
        $file = $this->themePath . '/' . ltrim($path, '/');
        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('share_images.%s in the theme\'s config.yml: %s does not exist.', $key, $file));
        }

        return $file;
    }
}
