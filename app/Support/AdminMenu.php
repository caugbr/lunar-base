<?php

namespace App\Support;

class AdminMenu
{
    protected static array $injectedSections = [];
    protected static array $injectedItems = [];
    protected static array $injectedSubItems = [];

    /**
     * Adiciona uma nova seção (grupo) ao menu lateral.
     *
     * @param string   $title Título do grupo/seção (ex: 'Ensino', 'Ferramentas')
     * @param array    $items Itens iniciais da seção (opcional)
     * @param int|null $index Posição numérica (0 = topo, 1 = segundo, null = final)
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
     * @param string|null $afterLabel Label do item após o qual inserir. Se null, vai pro final da seção.
     * @param int         $groupIndex Índice do grupo no menu (0 por padrão = grupo principal).
     */
    public static function add(array $item, ?string $afterLabel = null, int $groupIndex = 0): void
    {
        self::$injectedItems[] = [
            'item'       => $item,
            'after'      => $afterLabel,
            'groupIndex' => $groupIndex,
        ];
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
