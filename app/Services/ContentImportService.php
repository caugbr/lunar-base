<?php

namespace App\Services;

use App\Support\PublicationTypes;
use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class ContentImportService
{
    /**
     * Processa a importação a partir de um arquivo JSON enviado
     *
     * @param string $jsonFilePath Caminho do arquivo JSON
     * @param string $duplicateStrategy 'skip' (ignorar), 'overwrite' (sobrescrever), 'draft' (rascunho)
     * @return array Resumo estatístico do que foi importado
     */
    public function import(string $jsonFilePath, string $duplicateStrategy = 'skip'): array
    {
        if (!File::exists($jsonFilePath)) {
            throw new Exception("Arquivo de importação não encontrado.");
        }

        $jsonContent = File::get($jsonFilePath);
        $data = json_decode($jsonContent, true);

        if (!$data || !isset($data['manifest'])) {
            throw new Exception("O arquivo enviado não é um formato válido de exportação do Lunar Base.");
        }

        $stats = [
            'taxonomies' => 0,
            'terms'      => 0,
            'items'      => [],
        ];

        DB::beginTransaction();
        try {
            // 1. IMPORTA TAXONOMIAS E TERMOS PRIMEIRO
            $termMap = []; // Guarda [old_term_id => new_term_id] para reconectar relacionamentos

            if (!empty($data['taxonomies'])) {
                foreach ($data['taxonomies'] as $taxData) {
                    $taxonomy = Taxonomy::firstOrCreate(
                        ['slug' => $taxData['slug']],
                        [
                            'name'         => $taxData['name'],
                            'description'  => $taxData['description'] ?? null,
                            'hierarchical' => $taxData['hierarchical'] ?? false,
                            'unique'       => $taxData['unique'] ?? false,
                            'target_types' => $taxData['target_types'] ?? null,
                        ]
                    );
                    $stats['taxonomies']++;

                    // Importa os termos da taxonomia
                    if (!empty($taxData['terms'])) {
                        foreach ($taxData['terms'] as $termData) {
                            $term = Term::firstOrCreate(
                                [
                                    'taxonomy_id' => $taxonomy->id,
                                    'slug'        => $termData['slug'],
                                ],
                                [
                                    'name'        => $termData['name'],
                                    'description' => $termData['description'] ?? null,
                                ]
                            );

                            // Mapeia o ID antigo do JSON para o ID real no banco atual
                            if (isset($termData['id'])) {
                                $termMap[$termData['id']] = $term->id;
                            }
                            $stats['terms']++;
                        }
                    }
                }
            }

            // 2. IMPORTA O CONTEÚDO (POSTS, PÁGINAS, ETC.)
            $allTypes = PublicationTypes::all();

            if (!empty($data['content'])) {
                foreach ($data['content'] as $typeKey => $items) {
                    if (!isset($allTypes[$typeKey])) continue;

                    $modelClass = $allTypes[$typeKey]['model'];
                    if (!class_exists($modelClass)) continue;

                    $stats['items'][$typeKey] = ['created' => 0, 'updated' => 0, 'skipped' => 0];

                    foreach ($items as $itemData) {
                        $slug = $itemData['slug'] ?? null;
                        if (!$slug) continue;

                        $existing = $modelClass::where('slug', $slug)->first();

                        if ($existing) {
                            if ($duplicateStrategy === 'skip') {
                                $stats['items'][$typeKey]['skipped']++;
                                continue;
                            }

                            if ($duplicateStrategy === 'draft') {
                                $itemData['slug']   = $slug . '-import-' . Str::random(4);
                                $itemData['status'] = 'draft';
                                $existing = null; // Força criação de novo registro
                            }
                        }

                        // Remove chaves primárias do JSON antigo para não chocar com o auto-increment local
                        unset($itemData['id']);

                        // Extrai os termos e retira do payload do model
                        $oldTerms = $itemData['terms'] ?? [];
                        unset($itemData['terms'], $itemData['author'], $itemData['thumbnail']);

                        if ($existing && $duplicateStrategy === 'overwrite') {
                            $existing->update($itemData);
                            $model = $existing;
                            $stats['items'][$typeKey]['updated']++;
                        } else {
                            $model = $modelClass::create($itemData);
                            $stats['items'][$typeKey]['created']++;
                        }

                        // Reassocia os termos de taxonomia usando o mapa de IDs novos
                        if (!empty($oldTerms) && method_exists($model, 'terms')) {
                            $newTermIds = [];
                            foreach ($oldTerms as $oldTerm) {
                                $oldId = $oldTerm['id'] ?? null;
                                if ($oldId && isset($termMap[$oldId])) {
                                    $newTermIds[] = $termMap[$oldId];
                                }
                            }
                            $model->terms()->sync(array_unique($newTermIds));
                        }
                    }
                }
            }

            DB::commit();
            return $stats;

        } catch (Exception $e) {
            DB::rollBack();
            logger()->error("Erro na importação de conteúdo JSON: " . $e->getMessage());
            throw $e;
        }
    }
}
