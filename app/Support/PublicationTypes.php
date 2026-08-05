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
     * @param string       $key       Identificador único (ex: 'post', 'page', 'course')
     * @param array|string $data      Rótulo exibido (ex: 'Cursos') OU Array de configuração
     * @param string|null  $model     Classe do Model Eloquent (opcional)
     * @param array        $relations Relacionamentos para incluir na exportação (opcional)
     */
    public static function register(string $key, array|string $data, ?string $model = null, array $relations = []): void
    {
        if (is_string($data)) {
            $label = $data;
        } else {
            $label     = $data['label'] ?? ucfirst($key);
            $model     = $data['model'] ?? $model;
            $relations = $data['relations'] ?? $relations;
        }

        self::$types[$key] = [
            'key'       => $key,
            'label'     => $label,
            'model'     => $model,
            'relations' => $relations,
        ];
    }

    /**
     * Retorna todos os tipos registrados com suas configurações completas
     */
    public static function all(): array
    {
        if (!self::$booted) {
            // Tipos nativos do Core do Lunar Base registrados com rótulos explícitos
            self::register('post', 'Posts (Blog)', Post::class, ['author', 'thumbnail', 'terms']);
            self::register('page', 'Páginas', Page::class, ['author', 'thumbnail', 'terms']);

            self::$booted = true;
        }

        return self::$types;
    }

    /**
     * Retorna um array associativo simples [key => label]
     * Útil para renderizar checkboxes e dropdowns na Admin
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
