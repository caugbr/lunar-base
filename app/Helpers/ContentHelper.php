<?php

namespace App\Helpers;

class ContentHelper
{
    public static function parseShortcodes($content)
    {
        $content = preg_replace_callback('/<!--(script|style)>(.*?)<\/\1-->/is', function($matches) {
            return '<' . $matches[1] . '>' . $matches[2] . '</' . $matches[1] . '>';
        }, $content);

        return $content;
    }
}
