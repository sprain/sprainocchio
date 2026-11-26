<?php

namespace Sprainocchio\Twig;

// Escapes only what HTML needs: "<" (could start a tag), '"' (would end an attribute)
// and "&" where it could start a character reference ("&amp;", "&#039;").
// Everything else stays as written, so link previews that don't decode entities
// show "It's" and "Love > Money" instead of "It&#039;s" and "Love &gt; Money".
// Safe as long as templates quote attributes with double quotes.
class HtmlEscaper
{
    public const STRATEGY = 'html_minimal';

    public static function escape(mixed $string, string $charset = 'UTF-8') : string
    {
        $string = (string) $string;
        if ('UTF-8' !== strtoupper($charset)) {
            $string = mb_convert_encoding($string, 'UTF-8', $charset);
        }
        $string = mb_scrub($string, 'UTF-8');

        return preg_replace_callback(
            '/&(?=[A-Za-z0-9#])|[<"]/',
            static fn(array $m) : string => ['&' => '&amp;', '<' => '&lt;', '"' => '&quot;'][$m[0]],
            $string
        );
    }
}
