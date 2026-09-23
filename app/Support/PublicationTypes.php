<?php

namespace App\Support;

use App\Models\Post;
use App\Models\Page;

class PublicationTypes
{
    protected static array $types = [];
    protected static bool $booted = false;

    /**
     * Registra um tipo de publicação no sistema.
     *
     * @param string $key  Identificador único (ex: 'post', 'page', 'course')
     * @param array  $data Configurações do tipo
     */
    public static function register(string $key, array $data = []): void
    {
        self::$types[$key] = [
            'key'           => $key,
            'label'         => $data['label'] ?? ucfirst($key),
            'model'         => $data['model'] ?? null,
            'relations'     => $data['relations'] ?? [],
            'search_fields' => $data['search_fields'] ?? ['title', 'content'],
            'title_field'   => $data['title_field'] ?? 'title',
            // Permite que qualquer dado extra de plugins também seja preservado
            'extra'         => $data['extra'] ?? [],
        ];
    }

    /**
     * Retorna todos os tipos registrados com suas configurações completas
     */
    public static function all(): array
    {
        if (!self::$booted) {
            // Registro dos tipos nativos do Core do Lunar Base
            self::register('post', [
                'label'         => 'Posts (Blog)',
                'model'         => Post::class,
                'relations'     => ['author', 'thumbnail', 'terms'],
                'search_fields' => ['title', 'content'],
                'title_field'   => 'title',
            ]);

            self::register('page', [
                'label'         => 'Páginas',
                'model'         => Page::class,
                'relations'     => ['author', 'thumbnail', 'terms'],
                'search_fields' => ['title', 'content'],
                'title_field'   => 'title',
            ]);

            self::$booted = true;
        }

        return self::$types;
    }

    /**
     * Retorna um array associativo simples [key => label]
     * Útil para checkboxes e dropdowns na Admin
     */
    public static function labels(): array
    {
        return collect(self::all())->pluck('label', 'key')->toArray();
    }

    /**
     * Retorna as configurações de um tipo específico
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
