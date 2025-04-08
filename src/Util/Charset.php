<?php declare(strict_types=1);

namespace JkaServerStatus\Util;

class Charset
{
    /**
     * Converts a string to UTF-8, from the specified encoding.
     * @param string $string e.g. "\x80"
     * @param string $fromEncoding e.g. "Windows-1252"
     * @return false|string false if the conversion failed, the converted string otherwise (e.g. "€")
     */
    public static function toUtf8(string $string, string $fromEncoding): false|string
    {
        // You could swap iconv() for mb_convert_encoding() if you prefer.
        // That's why it's a good idea to centralize charset conversion.
        return iconv($fromEncoding, 'UTF-8//IGNORE', $string);
    }
}