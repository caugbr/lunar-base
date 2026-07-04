<?php

namespace App\Support;

class AdminMenu
{
    protected static array $injectedItems = [];

    public static function add(array $item, ?string $afterLabel = null)
    {
        self::$injectedItems[] = ['item' => $item, 'after' => $afterLabel];
    }

    public static function getInjectedItems(): array
    {
        return self::$injectedItems;
    }
}
