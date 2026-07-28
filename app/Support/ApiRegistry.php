<?php

namespace App\Support;

use App\Models\Post;
use App\Models\Page;
use App\Models\Taxonomy;
use App\Helpers\ContentHelper;

class ApiRegistry
{
    protected static array $entities = [];
    protected static bool $booted = false;

    /**
     * Registra uma nova entidade para ser servida pela API
     */
    public static function register(string $key, array $config): void
    {
        self::$entities[strtolower($key)] = $config;
    }

    /**
     * Retorna a configuração de uma entidade registrada
     */
    public static function get(string $key): ?array
    {
        self::bootCoreEntities();
        return self::$entities[strtolower($key)] ?? null;
    }

    /**
     * Retorna todas as chaves de entidades registradas
     */
    public static function keys(): array
    {
        self::bootCoreEntities();
        return array_keys(self::$entities);
    }

    /**
     * Inicializa os Schemas das entidades nativas do Core
     */
    protected static function bootCoreEntities(): void
    {
        if (self::$booted) return;

        // 1. SCHEMA DE POSTS
        self::register('posts', [
            'model'      => Post::class,
            'scope'      => fn($q) => $q->published()->feedOrder(),
            'key_column' => 'slug',
            'with'       => ['author', 'thumbnail', 'terms.taxonomy'],
            'searchable' => ['title', 'content', 'excerpt'],
            'schema'     => [
                'id'            => 'id',
                'title'         => 'title',
                'slug'          => 'slug',
                'excerpt'       => 'excerpt',
                'reading_time'  => 'reading_time',
                'published_at'  => 'published_at_formatted',
                'url'           => 'url',
                'author'        => [
                    'id'   => 'author.id',
                    'name' => 'author.name',
                ],
                'thumbnail'     => [
                    'url'   => 'thumbnail.url',
                    'thumb' => 'thumbnail.thumb_url',
                    'large' => 'thumbnail.large_url',
                    'alt'   => 'thumbnail.alt',
                ],
                'taxonomies'    => fn($post) => $post->terms
                    ->groupBy(fn($term) => $term->taxonomy?->slug ?? 'geral')
                    ->map(fn($terms) => $terms->map(fn($t) => [
                        'id'          => $t->id,
                        'name'        => $t->name,
                        'slug'        => $t->slug,
                        'description' => $t->description,
                    ])->values()->all())
                    ->toArray(),
                'content'       => fn($post) => [
                    'raw'      => $post->content,
                    'rendered' => ContentHelper::parseShortcodes($post->content),
                ],
            ]
        ]);

        // 2. SCHEMA DE PÁGINAS (Com suporte a Thumbnails completos e Taxonomias)
        self::register('pages', [
            'model'      => Page::class,
            'scope'      => fn($q) => $q->published(),
            'key_column' => 'slug',
            'with'       => ['author', 'thumbnail', 'terms.taxonomy'],
            'searchable' => ['title', 'content'],
            'schema'     => [
                'id'         => 'id',
                'title'      => 'title',
                'slug'       => 'slug',
                'namespace'  => 'namespace',
                'template'   => 'template',
                'url'        => 'url',
                'author'     => [
                    'id'   => 'author.id',
                    'name' => 'author.name',
                ],
                'thumbnail'  => [
                    'url'   => 'thumbnail.url',
                    'thumb' => 'thumbnail.thumb_url',
                    'large' => 'thumbnail.large_url',
                    'alt'   => 'thumbnail.alt',
                ],
                'taxonomies' => fn($page) => $page->terms
                    ->groupBy(fn($term) => $term->taxonomy?->slug ?? 'geral')
                    ->map(fn($terms) => $terms->map(fn($t) => [
                        'id'          => $t->id,
                        'name'        => $t->name,
                        'slug'        => $t->slug,
                        'description' => $t->description,
                    ])->values()->all())
                    ->toArray(),
                'content'    => fn($page) => [
                    'raw'      => $page->content,
                    'rendered' => ContentHelper::parseShortcodes($page->content),
                ],
            ]
        ]);

        // 3. SCHEMA DE TAXONOMIAS (CATEGORIAS, TAGS E OUTROS TERMOS)
        self::register('taxonomies', [
            'model'      => Taxonomy::class,
            'key_column' => 'slug',
            'with'       => ['terms'],
            'schema'     => [
                'id'          => 'id',
                'name'        => 'name',
                'slug'        => 'slug',
                'description' => 'description',
                'terms'       => fn($tax) => $tax->terms->map(fn($term) => [
                    'id'          => $term->id,
                    'name'        => $term->name,
                    'slug'        => $term->slug,
                    'description' => $term->description,
                ])->values()->all(),
            ]
        ]);

        self::$booted = true;
    }
}
