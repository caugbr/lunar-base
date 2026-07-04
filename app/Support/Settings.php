<?php

namespace App\Support;

class Settings
{
    protected static array $injectedItems  = [];
    protected static array $injectedGroups = [];

    /**
     * Registra um novo grupo de configurações.
     * Use ANTES de injetar campos nesse grupo via add().
     *
     * @param string $key  Chave do grupo (ex: 'forms', 'billing')
     * @param array  $meta Metadados do grupo: title, description, icon, tab
     */
    public static function addGroup(string $key, array $meta): void
    {
        self::$injectedGroups[$key] = array_merge([
            'title'       => ucfirst($key),
            'description' => '',
            'icon'        => 'settings',
            'fields'      => [],
        ], $meta);
    }

    /**
     * Injeta um campo em um grupo existente (config ou adicionado via addGroup).
     */
    public static function add(array $item, string $group, ?string $afterLabel = null): void
    {
        self::$injectedItems[] = [
            'item'  => $item,
            'group' => $group,
            'after' => $afterLabel,
        ];
    }

    public static function getInjectedGroups(): array
    {
        return self::$injectedGroups;
    }

    public static function getInjectedItems(): array
    {
        return self::$injectedItems;
    }
}
