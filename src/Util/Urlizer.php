<?php

namespace Sprainocchio\Util;

trait Urlizer
{
    // Explicit map instead of iconv's //TRANSLIT, whose output depends on the system's iconv library
    private const array TRANSLITERATIONS = [
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss', 'ẞ' => 'SS',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'å' => 'a', 'æ' => 'ae',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Å' => 'A', 'Æ' => 'Ae',
        'ç' => 'c', 'Ç' => 'C',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'ñ' => 'n', 'Ñ' => 'N',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'œ' => 'oe',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Œ' => 'Oe',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
        'ý' => 'y', 'ÿ' => 'y', 'Ý' => 'Y',
    ];

    public function urlize(string $string) : string
    {
        $string = strtr($string, self::TRANSLITERATIONS);
        $string = strtolower($string);
        $string = preg_replace('~[^a-z0-9_]+~', '-', $string);

        return trim($string, '-');
    }
}
