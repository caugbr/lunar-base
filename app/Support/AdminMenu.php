<?php

namespace App\Support;

class AdminMenu
{
    protected static array $injectedSections = [];
    protected static array $injectedItems = [];
    protected static array $injectedSubItems = [];

    /**
     * Adiciona uma nova seção (grupo) ao menu lateral.
     */
    public static function addSection(string $title, array $items = [], ?int $index = null): void
    {
        self::$injectedSections[] = [
            'title' => $title,
            'items' => $items,
            'index' => $index,
        ];
    }

    /**
     * Injeta um item de primeiro nível no menu lateral.
     *
     * @param array       $item       Dados do item (label, icon, route, active, etc.)
     * @param string|null $afterLabel Label do item referência (ex: 'Plugins').
     * @param int         $groupIndex Índice do grupo no menu (0 = principal).
     * @param int         $steps      Deslocamento relativo (+1 = após, -1 = antes).
     */
    public static function add(array $item, ?string $afterLabel = null, int $groupIndex = 0, int $steps = 1): void
    {
        self::$injectedItems[] = [
            'item'       => $item,
            'after'      => $afterLabel,
            'groupIndex' => $groupIndex,
            'steps'      => $steps,
        ];
    }

    /**
     * Atalho expressivo para inserir antes de um item de primeiro nível.
     */
    public static function addBefore(array $item, string $beforeLabel, int $groupIndex = 0): void
    {
        self::add($item, $beforeLabel, $groupIndex, steps: -1);
    }

    /**
     * Injeta um sub-item dentro de um item pai existente do menu.
     *
     * @param string      $parentLabel Label do item pai (ex: 'Logs', 'Páginas').
     * @param array       $subItem     Dados do sub-item (label, icon, route, active, etc.)
     * @param string|null $afterLabel  Label do sub-item referência.
     * @param int         $steps       Deslocamento relativo (+1 = após, -1 = antes).
     */
    public static function addSubItem(string $parentLabel, array $subItem, ?string $afterLabel = null, int $steps = 1): void
    {
        if (!isset(self::$injectedSubItems[$parentLabel])) {
            self::$injectedSubItems[$parentLabel] = [];
        }

        self::$injectedSubItems[$parentLabel][] = [
            'item'  => $subItem,
            'after' => $afterLabel,
            'steps' => $steps,
        ];
    }

    /**
     * Atalho expressivo para inserir antes de um sub-item existente.
     */
    public static function addSubItemBefore(string $parentLabel, array $subItem, string $beforeLabel): void
    {
        self::addSubItem($parentLabel, $subItem, $beforeLabel, steps: -1);
    }

    public static function getInjectedSections(): array
    {
        return self::$injectedSections;
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
