<?php

namespace Sprainocchio\Tests\ShareImage;

use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\TestCase;
use Sprainocchio\Post\Post;
use Sprainocchio\ShareImage\ShareImageRenderer;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[RequiresFunction('imagettftext')]
class ShareImageRendererTest extends TestCase
{
    protected string $public;

    protected function setUp() : void
    {
        $this->public = sys_get_temp_dir() . '/sprainocchio-share-' . uniqid();
        mkdir($this->public);
    }

    protected function tearDown() : void
    {
        exec('rm -rf ' . escapeshellarg($this->public));
    }

    public function testRendersImageForPost() : void
    {
        $path = $this->renderer()->render($this->post('Faire Preise'));

        $this->assertSame('share/201906/faire-preise.png', $path);
        $size = getimagesize($this->public . '/' . $path);
        $this->assertSame([1200, 630], [$size[0], $size[1]]);
        $this->assertSame('image/png', $size['mime']);
    }

    public function testDrawsTextInConfiguredColours() : void
    {
        $file = $this->public . '/' . $this->renderer(['background' => '#000', 'color' => '#ff0000'])->render($this->post('Faire Preise'));
        $image = imagecreatefrompng($file);

        $colours = [];
        for ($x = 0; $x < 1200; $x += 4) {
            for ($y = 0; $y < 630; $y += 4) {
                $colours[imagecolorat($image, $x, $y)] = true;
            }
        }

        $this->assertArrayHasKey(0x000000, $colours);
        $this->assertArrayHasKey(0xff0000, $colours);
    }

    public function testLongTitlesStillFit() : void
    {
        $path = $this->renderer()->render($this->post(str_repeat('Ein sehr langer Titel mit vielen Wörtern ', 12))->setSlug('lang'));

        $this->assertFileExists($this->public . '/' . $path);
    }

    public function testTemplatesSeePostAndGlobals() : void
    {
        $twig = new Environment(new ArrayLoader());
        $twig->addGlobal('theme', ['name' => 'Testblog']);
        $renderer = new class($twig, __DIR__ . '/fixtures', $this->public, [
            'font' => 'inter.ttf',
            'label' => '{% if post.author %}Von {{ post.author }}{% endif %} · {{ theme.name }}',
        ]) extends ShareImageRenderer {
            public function textFor(string $template, Post $post) : string
            {
                return $this->text($template, $post);
            }
        };

        $post = $this->post('A > B')->setAuthor('Claude');
        $this->assertSame('Von Claude · Testblog', $renderer->textFor('{% if post.author %}Von {{ post.author }}{% endif %} · {{ theme.name }}', $post));
        $this->assertSame('A > B', $renderer->textFor('{{ post.title }}', $post));
    }

    public function testMissingFontFails() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('share_images.font');

        $this->renderer(['font' => 'missing.ttf']);
    }

    protected function renderer(array $config = []) : ShareImageRenderer
    {
        return new ShareImageRenderer(
            new Environment(new ArrayLoader()),
            __DIR__ . '/fixtures',
            $this->public,
            $config + ['font' => 'inter.ttf']
        );
    }

    protected function post(string $title) : Post
    {
        return (new Post())->setTitle($title)->setDate(new \DateTime('2019-06-17'));
    }
}
