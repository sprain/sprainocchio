<?php

namespace Sprainocchio\Tests\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sprainocchio\Util\Urlizer;

class UrlizerTest extends TestCase
{
    use Urlizer;

    public static function provideStrings() : iterable
    {
        yield 'plain title' => ['Vom 2015er-Macbook Pro aufs Macbook Pro 16', 'vom-2015er-macbook-pro-aufs-macbook-pro-16'];
        yield 'german umlauts' => ['iOS-Shortcut für die nächste öV-Verbindung', 'ios-shortcut-fuer-die-naechste-oev-verbindung'];
        yield 'capital umlauts and sharp s' => ['Ärger Öl Über Straße ẞ', 'aerger-oel-ueber-strasse-ss'];
        yield 'french accents' => ['Œuvre, Café & Garçon', 'oeuvre-cafe-garcon'];
        yield 'symbols become hyphens' => ['100% ★ ok?', '100-ok'];
        yield 'underscores are kept' => ['snake_case Test', 'snake_case-test'];
        yield 'nothing left' => ['---', ''];
    }

    #[DataProvider('provideStrings')]
    public function testUrlize(string $string, string $expected) : void
    {
        $this->assertSame($expected, $this->urlize($string));
    }
}
