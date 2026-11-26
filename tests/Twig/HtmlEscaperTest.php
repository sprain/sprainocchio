<?php

namespace Sprainocchio\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Sprainocchio\Twig\HtmlEscaper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Runtime\EscaperRuntime;

class HtmlEscaperTest extends TestCase
{
    public function testLeavesHarmlessCharactersAlone() : void
    {
        $this->assertSame("It's Love > Money & more – «Zürich»", HtmlEscaper::escape("It's Love > Money & more – «Zürich»"));
    }

    public function testEscapesHtmlSyntax() : void
    {
        $this->assertSame('&lt;b> &quot;x&quot;', HtmlEscaper::escape('<b> "x"'));
        $this->assertSame('AT&amp;T &amp;#039; &amp;amp;', HtmlEscaper::escape('AT&T &#039; &amp;'));
    }

    public function testReplacesInvalidUtf8() : void
    {
        $this->assertSame('a?b', HtmlEscaper::escape("a\xC3b"));
    }

    public function testIsTwigsAutoescapeStrategy() : void
    {
        $twig = new Environment(new ArrayLoader([
            't' => '<title>{{ t }}</title><meta content="{{ t }}">{{ h|raw }}',
        ]), ['autoescape' => HtmlEscaper::STRATEGY]);
        $twig->getRuntime(EscaperRuntime::class)->setEscaper(HtmlEscaper::STRATEGY, HtmlEscaper::escape(...));

        $this->assertSame(
            '<title>Love > &quot;Money&quot;\'s</title><meta content="Love > &quot;Money&quot;\'s"><p>ok</p>',
            $twig->render('t', ['t' => 'Love > "Money"\'s', 'h' => '<p>ok</p>'])
        );
    }
}
