<?php

namespace App\Support;

class PublicationTypes
{
    protected static array $types = [];
    protected static bool $booted = false;

    public static function register(string $key, string $label): void
    {
        self::$types[$key] = $label;
    }

    public static function all(): array
    {
        if (!self::$booted) {
            // Tipos nativos do Core do Lunar Base
            self::register('post', 'Posts');
            self::register('page', 'Páginas');
            self::$booted = true;
        }

        return self::$types;
    }
}
