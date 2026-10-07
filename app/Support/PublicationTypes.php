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
                'label'         => 'Posts',
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

    /**
     * Registra automaticamente os hooks de todos os tipos no ciclo de vida do Eloquent.
     */
    public static function bootHooks(): void
    {
        foreach (self::all() as $key => $config) {
            $modelClass = $config['model'] ?? null;

            if ($modelClass && class_exists($modelClass)) {

                // ==========================================
                // APENAS NA CRIAÇÃO (INSERT / Store)
                // ==========================================
                $modelClass::creating(function ($model) use ($key) {
                    $model = applyFilters('publication_creating', $model, $key);
                    if ($model === false) return false;

                    $model = applyFilters("{$key}_creating", $model);
                    if ($model === false) return false;
                });

                $modelClass::created(function ($model) use ($key) {
                    doAction('publication_created', $model, $key);
                    doAction("{$key}_created", $model);
                });

                // ==========================================
                // APENAS NA ATUALIZAÇÃO (UPDATE / Update)
                // ==========================================
                $modelClass::updating(function ($model) use ($key) {
                    $model = applyFilters('publication_updating', $model, $key);
                    if ($model === false) return false;

                    $model = applyFilters("{$key}_updating", $model);
                    if ($model === false) return false;
                });

                $modelClass::updated(function ($model) use ($key) {
                    doAction('publication_updated', $model, $key);
                    doAction("{$key}_updated", $model);
                });

                // ==========================================
                // SALVAMENTO GERAL (INSERT + UPDATE)
                // ==========================================
                $modelClass::saving(function ($model) use ($key) {
                    $model = applyFilters('publication_saving', $model, $key);
                    if ($model === false) return false;

                    $model = applyFilters("{$key}_saving", $model);
                    if ($model === false) return false;
                });

                $modelClass::saved(function ($model) use ($key) {
                    doAction('publication_saved', $model, $key);
                    doAction("{$key}_saved", $model);
                });

                // ==========================================
                // EXCLUSÃO (DELETE / Destroy)
                // ==========================================
                $modelClass::deleting(function ($model) use ($key) {
                    // Mudamos para applyFilters para permitir barrar
                    $result = applyFilters('publication_deleting', $model, $key);
                    if ($result === false) return false;

                    $result = applyFilters("{$key}_deleting", $model);
                    if ($result === false) return false;
                });

                $modelClass::deleted(function ($model) use ($key) {
                    doAction('publication_deleted', $model, $key);
                    doAction("{$key}_deleted", $model);
                });
            }
        }
    }
}
