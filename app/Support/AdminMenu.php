<?php

namespace App\Support;

class AdminMenu
{
    protected static array $injectedItems = [];
    protected static array $injectedSubItems = [];

    /**
     * Injeta um item de primeiro nível no menu lateral.
     *
     * @param array       $item       Dados do item (label, icon, route, active, etc.)
     * @param string|null $afterLabel Label do item após o qual inserir. Se null, vai pro final.
     */
    public static function add(array $item, ?string $afterLabel = null): void
    {
        self::$injectedItems[] = ['item' => $item, 'after' => $afterLabel];
    }

    /**
     * Injeta um sub-item dentro de um item pai existente do menu.
     *
     * @param string      $parentLabel Label do item pai (ex: 'Logs', 'Páginas').
     * @param array       $subItem     Dados do sub-item (label, icon, route, active, etc.)
     * @param string|null $afterLabel  Label do sub-item após o qual inserir. Se null, vai pro final.
     */
    public static function addSubItem(string $parentLabel, array $subItem, ?string $afterLabel = null): void
    {
        if (!isset(self::$injectedSubItems[$parentLabel])) {
            self::$injectedSubItems[$parentLabel] = [];
        }

        self::$injectedSubItems[$parentLabel][] = ['item' => $subItem, 'after' => $afterLabel];
    }

    public static function getInjectedItems(): array
    {
        return self::$injectedItems;
    }

    public static function getInjectedSubItems(): array
    {
        return self::$injectedSubItems;
    }
}
